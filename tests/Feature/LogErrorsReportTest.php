<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\Admin\LogErrorsFound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LogErrorsReportTest extends TestCase
{
    use RefreshDatabase;

    private string $log;

    private string $state;

    protected function setUp(): void
    {
        parent::setUp();

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'log-report-' . uniqid();
        mkdir($dir);
        $this->log = $dir . DIRECTORY_SEPARATOR . 'laravel.log';
        $this->state = $dir . DIRECTORY_SEPARATOR . 'state.json';
        file_put_contents($this->log, $this->record('ERROR', 'Stará chyba spred nasadenia'));

        config([
            'logging.report.path' => $this->log,
            'logging.report.state' => $this->state,
            'logging.report.email' => null,
        ]);

        Notification::fake();
    }

    private function record(string $level, string $message, string $class = 'Error', string $file = 'app/Http/Controllers/HomeController.php'): string
    {
        $path = str_replace('/', '\\\\', base_path() . '/' . $file);
        $class = str_replace('\\', '\\\\', $class);

        return "[2026-10-23 14:05:01] production.{$level}: {$message} {\"exception\":\"[object] ({$class}(code: 0): {$message} at {$path}:42)\n"
            . "[stacktrace]\n#0 {$path}(42): foo()\n#1 {main}\n\"}\n";
    }

    private function append(string $text): void
    {
        file_put_contents($this->log, $text, FILE_APPEND);
    }

    private function superadmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        return $admin;
    }

    public function test_prvy_beh_nehlasi_staru_historiu(): void
    {
        $this->superadmin();

        $this->artisan('logs:report-errors')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertFileExists($this->state);
    }

    public function test_opravitelne_chyby_pridu_jednym_mailom_sumom_nie(): void
    {
        $admin = $this->superadmin();
        $this->artisan('logs:report-errors');

        $this->append($this->record('ERROR', 'Class "App\\Models\\AppSupportMediaUrl" not found'));
        $this->append($this->record('ERROR', 'Class "App\\Models\\AppSupportMediaUrl" not found'));
        $this->append($this->record('ERROR', "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'api_token'", 'Illuminate\\Database\\QueryException'));
        $this->append($this->record('WARNING', 'Import videí z YouTube zlyhal'));
        $this->append($this->record('INFO', 'Všetko v poriadku'));
        $this->append($this->record('ERROR', 'cURL error 7: Failed to connect to api.pwnedpasswords.com:443', 'Illuminate\\Http\\Client\\ConnectionException'));
        $this->append($this->record('ERROR', 'YouTube API nedostupné: chyba siete'));
        $this->append($this->record('ERROR', 'The "--compact" option does not exist.', 'Symfony\\Component\\Console\\Exception\\RuntimeException'));

        $this->artisan('logs:report-errors')->assertSuccessful();

        Notification::assertSentTo($admin, LogErrorsFound::class, function (LogErrorsFound $n) {
            $this->assertCount(2, $n->groups);
            $this->assertSame(2, $n->groups[0]['count']);
            $this->assertStringContainsString('AppSupportMediaUrl', $n->groups[0]['message']);
            $this->assertSame('app/Http/Controllers/HomeController.php:42', $n->groups[0]['location']);
            $this->assertSame('Illuminate\\Database\\QueryException', $n->groups[1]['class']);

            return true;
        });
        Notification::assertSentTimes(LogErrorsFound::class, 1);

        // Bez nových záznamov sa nič neposiela znova.
        $this->artisan('logs:report-errors')->assertSuccessful();
        Notification::assertSentTimes(LogErrorsFound::class, 1);
    }

    public function test_len_sum_neposle_nic(): void
    {
        $this->superadmin();
        $this->artisan('logs:report-errors');

        $this->append($this->record('ERROR', 'cURL error 28: Resolving timed out', 'Illuminate\\Http\\Client\\ConnectionException'));

        $this->artisan('logs:report-errors')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_vyprazdneny_log_sa_cita_od_zaciatku(): void
    {
        $this->superadmin();
        $this->append(str_repeat('[2026-10-23 14:00:00] production.INFO: výplň' . "\n", 50));
        $this->artisan('logs:report-errors');

        file_put_contents($this->log, $this->record('ERROR', 'Call to undefined method Post::foo()'));

        $this->artisan('logs:report-errors')->assertSuccessful();

        Notification::assertSentTimes(LogErrorsFound::class, 1);
    }

    public function test_adresa_z_konfiguracie_ma_prednost(): void
    {
        config(['logging.report.email' => 'dev@example.com']);
        $this->artisan('logs:report-errors');

        $this->append($this->record('ERROR', 'Array to string conversion'));
        $this->artisan('logs:report-errors')->assertSuccessful();

        Notification::assertSentTo(new AnonymousNotifiable, LogErrorsFound::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === ['dev@example.com']);
    }

    public function test_mail_sa_vykresli(): void
    {
        $html = (string) (new LogErrorsFound([[
            'message' => 'Class "X" not found', 'class' => 'Error', 'location' => 'app/X.php:1',
            'count' => 3, 'first' => '2026-10-23 14:00:00', 'last' => '2026-10-23 14:05:01',
        ]]))->toMail($this->superadmin())->render();

        $this->assertStringContainsString('Class "X" not found<br>Error · app/X.php:1', $html);
        $this->assertStringContainsString('3× · naposledy 2026-10-23 14:05:01', $html);
    }
}
