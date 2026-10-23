<?php

namespace App\Console\Commands;

use App\Notifications\Admin\LogErrorsFound;
use App\Services\Youtube\NotifyAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Pošle e-mailom chyby, ktoré pribudli v laravel.log od posledného behu.
 * Log sa predtým kontroloval ručne a chyby v kóde (chýbajúca trieda,
 * neexistujúci stĺpec) v ňom ležali dni.
 *
 * Mail neodchádza v okamihu chyby, ale z plánovača: funguje aj pri výpadku
 * DB či cache (pozícia v logu je v súbore), nezacyklí sa pri chybe pošty
 * a tisíc rovnakých chýb je jeden riadok s počtom. Šum z výpadkov cudzích
 * služieb sa vynechá (config/logging.php → report.ignore).
 */
class LogErrorsReport extends Command
{
    protected $signature = 'logs:report-errors
        {--dry-run : Len vypísať, čo by sa poslalo, a pozíciu neposunúť}';

    protected $description = 'Email new fixable errors from laravel.log';

    private const LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /** Hlavička záznamu Monologu: [2026-10-23 14:05:01] production.ERROR: správa */
    private const HEADER = '/^\[(\d{4}-\d\d-\d\d[ T][^\]]*)\] ([\w.-]+)\.([A-Z]+): (.*)$/m';

    public function handle(): int
    {
        $config = config('logging.report');

        if (! $config['enabled']) {
            return self::SUCCESS;
        }

        $path = $config['path'];
        $size = is_file($path) ? (int) filesize($path) : 0;
        $state = $this->readState($config['state']);

        // Prvý beh nehlási celú históriu logu, len si zapamätá jeho koniec.
        if ($state === null) {
            $this->option('dry-run') || $this->writeState($config['state'], $size);
            $this->info("LogErrorsReport: začínam od pozície {$size}.");

            return self::SUCCESS;
        }

        // Log zmazaný alebo vyprázdnený — číta sa od začiatku.
        $offset = $state > $size ? 0 : $state;

        if ($offset === $size) {
            return self::SUCCESS;
        }

        [$chunk, $next] = $this->readChunk($path, $offset, $size, (int) $config['max_bytes']);
        $groups = $this->groups($chunk, $config['ignore']);

        if ($this->option('dry-run')) {
            foreach ($groups as $group) {
                $this->line("{$group['count']}× {$group['message']}" . ($group['location'] ? " ({$group['location']})" : ''));
            }
            $this->info('LogErrorsReport: ' . count($groups) . ' druhov chýb, nič sa neposlalo.');

            return self::SUCCESS;
        }

        if ($groups !== []) {
            $recipients = $this->recipients();

            if ($recipients === null) {
                $this->warn('LogErrorsReport: niet komu poslať (LOG_REPORT_EMAIL ani superadmin).');

                return self::FAILURE;
            }

            // Keď mail zlyhá, pozícia sa neposunie a chyby prídu nabudúce.
            $recipients(new LogErrorsFound($groups));
        }

        $this->writeState($config['state'], $next);
        $this->info('LogErrorsReport: ' . count($groups) . ' druhov chýb.');

        return self::SUCCESS;
    }

    /**
     * @return array{string, int} prečítaný text a pozícia, kde nabudúce pokračovať
     */
    private function readChunk(string $path, int $offset, int $size, int $maxBytes): array
    {
        $handle = fopen($path, 'rb');
        fseek($handle, $offset);
        $chunk = (string) fread($handle, min($maxBytes, $size - $offset));
        fclose($handle);

        // Ak sa nečítalo do konca, posledný (možno useknutý) záznam počká.
        if ($offset + strlen($chunk) < $size && ($cut = strrpos($chunk, "\n[")) !== false) {
            $chunk = substr($chunk, 0, $cut + 1);
        }

        return [$chunk, $offset + strlen($chunk)];
    }

    /**
     * Chyby zoskupené podľa správy bez čísel (ID, časy, porty), najčastejšie prvé.
     *
     * @param  list<string>  $ignore
     * @return list<array{message: string, class: ?string, location: ?string, count: int, first: string, last: string}>
     */
    private function groups(string $chunk, array $ignore): array
    {
        $records = preg_split('/^(?=\[\d{4}-\d\d-\d\d[ T][^\]]*\] [\w.-]+\.[A-Z]+: )/m', $chunk, -1, PREG_SPLIT_NO_EMPTY);
        $groups = [];

        foreach ($records as $record) {
            if (! preg_match(self::HEADER, $record, $header) || ! in_array($header[3], self::LEVELS, true)) {
                continue;
            }

            $message = trim(Str::before($header[4], ' {"'));
            $class = preg_match('/\[object\] \(([\w\\\\]+)\(code: /', $record, $m) ? stripslashes($m[1]) : null;

            if ($this->ignored($message . ' ' . $class, $ignore)) {
                continue;
            }

            $message = $message !== '' ? $message : (string) $class;
            $key = md5($class . '|' . Str::limit(preg_replace('/\d+/', 'N', $message), 200));

            $groups[$key] ??= [
                'message' => Str::limit($message, 300),
                'class' => $class,
                'location' => $this->location($record),
                'count' => 0,
                'first' => $header[1],
            ];
            $groups[$key]['count']++;
            $groups[$key]['last'] = $header[1];
        }

        usort($groups, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $groups;
    }

    /** @param  list<string>  $ignore */
    private function ignored(string $haystack, array $ignore): bool
    {
        foreach ($ignore as $needle) {
            if ($needle !== '' && stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prvé miesto v kóde portálu (app/, routes/, resources/, config/, database/),
     * kde chyba nastala — vendor nič nepovie.
     */
    private function location(string $record): ?string
    {
        $base = preg_quote(base_path() . DIRECTORY_SEPARATOR, '/');
        $own = '(?:app|routes|resources|config|database|bootstrap)[\\\\\/][^\s:()]+';

        $patterns = [
            "/ at {$base}({$own}):(\d+)\)/",
            "/#\d+ {$base}({$own})\((\d+)\)/",
            "/ in {$base}({$own}):(\d+)/",
        ];

        $record = str_replace('\\\\', '\\', $record);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $record, $m)) {
                return str_replace('\\', '/', $m[1]) . ':' . $m[2];
            }
        }

        return null;
    }

    /** @return (callable(LogErrorsFound): void)|null */
    private function recipients(): ?callable
    {
        $emails = array_filter(array_map('trim', explode(',', (string) config('logging.report.email'))));

        if ($emails !== []) {
            return fn ($notification) => Notification::route('mail', $emails)->notify($notification);
        }

        $admins = NotifyAdmin::superadmins();

        return $admins->isEmpty() ? null : fn ($notification) => Notification::send($admins, $notification);
    }

    private function readState(string $file): ?int
    {
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        return isset($data['offset']) ? (int) $data['offset'] : null;
    }

    private function writeState(string $file, int $offset): void
    {
        file_put_contents($file, json_encode(['offset' => $offset, 'at' => now()->toIso8601String()]));
    }
}
