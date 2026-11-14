<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Správa oznamov. Celá skupina `admin.` beží za middleware `checkSuperAdmin`
 * (routes/web.php), takže sem sa iný než superadmin nedostane.
 */
class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $placement = AnnouncementPlacement::tryFrom((string) $request->input('placement'));

        $announcements = Announcement::query()
            ->when($placement, fn ($query) => $query->placement($placement))
            ->orderBy('placement')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admins.announcements.index', [
            'announcements' => $announcements,
            'placement'     => $placement,
            'placements'    => AnnouncementPlacement::options(),
        ]);
    }

    public function create()
    {
        return view('admins.announcements.create', [
            'announcement' => new Announcement([
                'placement'   => AnnouncementPlacement::Top,
                'variant'     => AnnouncementVariant::Info,
                'active'      => true,
                'dismissible' => false,
                'sort_order'  => 0,
            ]),
            'placements' => AnnouncementPlacement::options(),
            'variants'   => AnnouncementVariant::options(),
        ]);
    }

    public function store(AnnouncementRequest $request)
    {
        $announcement = new Announcement($request->payload());
        $this->syncImages($request, $announcement);
        $announcement->save();

        return redirect()
            ->route('admin.announcement.index')
            ->with('flash', 'Oznam „' . $announcement->title . '“ je uložený.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admins.announcements.edit', [
            'announcement' => $announcement,
            'placements'   => AnnouncementPlacement::options(),
            'variants'     => AnnouncementVariant::options(),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        $announcement->fill($request->payload());
        $this->syncImages($request, $announcement);
        $announcement->save();

        return redirect()
            ->route('admin.announcement.index')
            ->with('flash', 'Zmeny v ozname sú uložené.');
    }

    /**
     * Rýchle zapnutie a vypnutie priamo z výpisu. Text oznamu zostáva uložený,
     * mení sa len to, či sa vypisuje na webe.
     */
    public function toggle(Announcement $announcement)
    {
        $announcement->update(['active' => ! $announcement->active]);

        return back()->with(
            'flash',
            $announcement->active ? 'Oznam sa zobrazuje.' : 'Oznam je vypnutý.'
        );
    }

    /**
     * Uloží nahraté banery a zmaže tie, ktoré správca nahradil alebo
     * odstránil. Súbor ide na disk bez prepočítania — akékoľvek spracovanie
     * cez GD by z animovaného GIF/WebP spravilo statický obrázok.
     */
    private function syncImages(AnnouncementRequest $request, Announcement $announcement): void
    {
        $disk = Storage::disk('public');

        foreach (['image' => 'image', 'mobile_image' => 'mobile_image'] as $input => $prefix) {
            $pathField = $prefix . '_path';
            $old = $announcement->getOriginal($pathField);
            $file = $request->file($input);

            if ($file instanceof UploadedFile) {
                $size = @getimagesize($file->getRealPath()) ?: [null, null];

                $announcement->forceFill([
                    $pathField          => $file->store(Announcement::IMAGE_DIR, 'public'),
                    $prefix . '_width'  => $size[0],
                    $prefix . '_height' => $size[1],
                ]);
            } elseif ($request->boolean('remove_' . $input)) {
                $announcement->forceFill([$pathField => null, $prefix . '_width' => null, $prefix . '_height' => null]);
            } else {
                continue;
            }

            if ($old && $old !== $announcement->{$pathField}) {
                $disk->delete($old);
            }
        }
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcement.index')
            ->with('flash', 'Oznam je zmazaný.');
    }
}
