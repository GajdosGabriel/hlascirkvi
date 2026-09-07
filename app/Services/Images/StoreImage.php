<?php

namespace App\Services\Images;

use App\Enums\ImageSize;
use App\Models\Image as ImageModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Throwable;

/**
 * Jediná cesta, ktorou sa obrázok dostáva k modelu.
 *
 * Nahradzuje tri takmer zhodné kópie (App\Services\ImageResize,
 * App\Services\Files\File a App\Services\Files\FileYoutube), ktoré sa líšili
 * veľkosťami, významom stĺpcov aj tým, či názov súboru mal príponu.
 *
 * Poradie krokov je podstatné: súbory sa zapíšu ako prvé a záznam v DB vzniká
 * až nad hotovými súbormi. Predtým to bolo naopak, takže po zlyhaní zmenšenia
 * ostal v tabuľke riadok ukazujúci na neexistujúci náhľad.
 */
final class StoreImage
{
    /** @var array<int, string> cesty zapísané v tomto behu, pre úklid po výnimke */
    private array $written = [];

    /** @var array{0: int, 1: int} rozmery najväčšieho variantu, teda toho v url */
    private array $dimensions = [0, 0];

    public function __construct(private readonly Model $model)
    {
    }

    public static function for(Model $model): self
    {
        return new self($model);
    }

    public function fromUpload(UploadedFile $file): ImageModel
    {
        return $this->store(ImageSource::fromUpload($file));
    }

    public function fromUrl(string $url): ImageModel
    {
        return $this->store(ImageSource::fromUrl($url));
    }

    /**
     * Pre dávkové importy: jeden pokazený náhľad nesmie zhodiť celý beh cronu,
     * ale nesmie ani zmiznúť bez stopy.
     */
    public function tryFromUrl(?string $url): ?ImageModel
    {
        if (blank($url)) {
            return null;
        }

        try {
            return $this->fromUrl($url);
        } catch (Throwable $e) {
            Log::warning('Obrázok sa nepodarilo uložiť.', [
                'model' => $this->model::class,
                'id' => $this->model->getKey(),
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function store(ImageSource $source): ImageModel
    {
        // Rozmery sa čítajú z hlavičky ešte pred dekódovaním – GD by inak
        // stihol alokovať celý raster a poistka by prišla neskoro.
        $this->guardAgainstOversizedRaster($source->binary);

        $image = Image::decodeBinary($source->binary);

        // Fotky z mobilu nesú natočenie v EXIF a GD ho pri prekódovaní zahodí.
        // Bez tohto kroku by skončili na stránke otočené nabok.
        $image->orient();

        $this->written = [];

        try {
            $variants = $this->writeVariants($image, $this->baseName());

            return DB::transaction(fn () => $this->record($source, $variants, $this->dimensions));
        } catch (Throwable $e) {
            $this->cleanUp();

            throw $e;
        }
    }

    /**
     * Zmenšuje sa postupne od najväčšieho variantu, takže raster prechádza
     * pamäťou raz. Klonovať Intervention obrázok sa nedá – GD zdroj by ostal
     * zdieľaný a druhá kópia by pracovala nad už zmenšeným rastrom.
     *
     * @return array<string, array<int, string>>
     */
    private function writeVariants(ImageInterface $image, string $base): array
    {
        $disk = $this->disk();
        $encoders = [
            'jpg' => new JpegEncoder(quality: (int) config('images.quality.jpeg')),
            'webp' => new WebpEncoder(quality: (int) config('images.quality.webp')),
        ];

        $variants = [];

        foreach (ImageSize::forWidth($image->width()) as $size) {
            $image->scaleDown(width: $size->value);

            // Kľúčom je skutočná šírka, nie cieľová. Predloha užšia než cieľ
            // sa nezväčšuje, takže by inak 320 px súbor vystupoval v srcset
            // ako 1200w a prehliadač by ho vybral pre veľké miesto.
            $width = $image->width();

            // Prvý priechod je najväčší variant a práve naň ukazuje stĺpec url.
            if ($variants === []) {
                $this->dimensions = [$width, $image->height()];
            }

            foreach ($encoders as $extension => $encoder) {
                $path = $this->folder() . $base . '-w' . $width . '.' . $extension;

                $disk->put($path, $image->encode($encoder)->toString());

                $this->written[] = $path;
                $variants[$extension][$width] = $path;
            }
        }

        if ($variants === []) {
            throw new RuntimeException('Nevznikol žiadny variant obrázka.');
        }

        return $variants;
    }

    /**
     * @param array<string, array<int, string>> $variants
     * @param array{0: int, 1: int} $dimensions
     */
    private function record(ImageSource $source, array $variants, array $dimensions): ImageModel
    {
        $jpg = $variants['jpg'];

        return $this->model->images()->create([
            'name' => Str::limit((string) ($this->model->title ?? $this->model->slug ?? ''), 250, ''),
            'url' => reset($jpg),
            'thumb' => end($jpg),
            'org_name' => Str::limit((string) ($this->model->organization?->title ?? $source->originalName), 190, ''),
            'size' => strlen($source->binary),
            'mime' => 'jpg',
            'type' => 'img',
            'is_primary' => ! $this->model->images()->exists(),
            'variants' => $variants,
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ]);
    }

    private function guardAgainstOversizedRaster(string $binary): void
    {
        $info = @getimagesizefromstring($binary);

        if ($info === false) {
            throw new RuntimeException('Súbor nie je obrázok.');
        }

        $pixels = $info[0] * $info[1];
        $max = (int) config('images.max_pixels');

        if ($pixels > $max) {
            throw new RuntimeException("Obrázok má {$pixels} px, povolené maximum je {$max} px.");
        }
    }

    /**
     * Názov je zložený zo slugu (kvôli čitateľnosti v úložisku) a ULID, ktorý
     * kolíziu vylučuje. Predtým sa spoliehalo na dve rand() a súbor sa ukladal
     * úplne bez prípony, takže ho webserver posielal ako octet-stream.
     */
    private function baseName(): string
    {
        $slug = Str::slug((string) ($this->model->slug ?? $this->model->title ?? 'obrazok'));

        return Str::limit($slug ?: 'obrazok', 80, '') . '-' . Str::lower((string) Str::ulid());
    }

    /**
     * Rok a mesiac v ceste držia priečinky v rozumnej veľkosti. Bez nich mala
     * najväčšia organizácia 8 255 súborov v jednom adresári a pri šiestich
     * variantoch na obrázok by ich tam pribudlo desaťnásobne.
     *
     * Staré obrázky majú cestu uloženú v DB, takže im to nevadí.
     */
    private function folder(): string
    {
        $date = $this->model->created_at ?? now();

        return Str::lower(class_basename($this->model)) . 's/'
            . $this->model->organization_id . '/'
            . $date->format('Y') . '/'
            . $date->format('m') . '/';
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk(config('images.disk'));
    }

    private function cleanUp(): void
    {
        if ($this->written === []) {
            return;
        }

        $this->disk()->delete($this->written);
        $this->written = [];
    }
}
