<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

/**
 * Zvonček v navigácii (resources/js/navigation/Bell.vue). Doteraz sa dala
 * notifikácia iba označiť za prečítanú — v zozname tak zostávali navždy
 * napríklad desiatky rovnakých hlásení o spamovej modlitbe a nedalo sa
 * s nimi nič urobiť. Preto tu pribudlo čítanie zoznamu, mazanie jednotlivej
 * položky aj hromadné akcie.
 *
 * Každý dopyt ide cez $request->user()->notifications(), takže sa užívateľ
 * nedostane k cudzej notifikácii ani keď uhádne UUID.
 */
class NotificationController extends Controller
{
    /** Koľko položiek vráti jedna strana zvončeka. */
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(self::PER_PAGE);

        return NotificationResource::collection($notifications)
            ->additional(['meta' => [
                'unread' => $request->user()->unreadNotifications()->count(),
            ]]);
    }

    /**
     * Označenie prečítané / neprečítané. Bez tela požiadavky sa notifikácia
     * označí za prečítanú — tak to volá kliknutie na položku v zvončeku.
     */
    public function update(Request $request, string $notification)
    {
        $request->validate(['read' => 'sometimes|boolean']);

        $notify = $request->user()->notifications()->findOrFail($notification);

        $request->boolean('read', true)
            ? $notify->markAsRead()
            : $notify->markAsUnread();

        return new NotificationResource($notify);
    }

    public function destroy(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->delete();

        return response()->noContent();
    }

    /**
     * Hromadné označenie prečítané. Bez `ids` označí všetko neprečítané,
     * s `ids` len vybrané — zvonček takto vybaví jedným dopytom celú skupinu
     * zlúčených rovnakých hlásení.
     */
    public function markRead(Request $request)
    {
        $notifications = $request->user()->unreadNotifications();

        if ($ids = $this->ids($request)) {
            $notifications->whereIn('id', $ids);
        }

        $notifications->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function markUnread(Request $request)
    {
        $notifications = $request->user()->notifications()->whereNotNull('read_at');

        if ($ids = $this->ids($request)) {
            $notifications->whereIn('id', $ids);
        }

        $notifications->update(['read_at' => null]);

        return response()->noContent();
    }

    /**
     * Hromadné mazanie. Bez parametrov zmaže všetko, `ids` zmaže vybrané
     * (zoskupené duplicitné hlásenia v zvončeku sú viac záznamov naraz)
     * a `only=read` upratovanie prečítaných.
     */
    public function destroyAll(Request $request)
    {
        $notifications = $request->user()->notifications();

        if ($ids = $this->ids($request)) {
            $notifications->whereIn('id', $ids);
        } elseif ($request->input('only') === 'read') {
            $notifications->whereNotNull('read_at');
        }

        $notifications->delete();

        return response()->noContent();
    }

    /**
     * Zoznam UUID z tela požiadavky, alebo prázdne pole pre „všetko".
     *
     * @return array<int, string>
     */
    private function ids(Request $request): array
    {
        $request->validate([
            'ids' => 'sometimes|array',
            'ids.*' => 'string',
            'only' => 'sometimes|in:read',
        ]);

        return array_values(array_filter((array) $request->input('ids', [])));
    }
}
