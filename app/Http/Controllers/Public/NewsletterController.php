<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SystemLog\Recorder;
use Illuminate\Http\Request;

/**
 * Odber mesačného newslettera. Odhlásenie z e-mailu ide bez prihlásenia —
 * oprávnenie nesie podpis v URL (middleware `signed` v routes/web.php), ktorý
 * generuje App\Mail\PostNewsletter. Prihlásený používateľ ho prepína aj na
 * stránke /odber-noviniek.
 */
class NewsletterController extends Controller
{
    public function show(Request $request, User $user)
    {
        return $this->page($user);
    }

    /**
     * POST príde z tlačidla na stránke aj z poštového servera (RFC 8058,
     * `List-Unsubscribe=One-Click`). Opakované volanie je bez účinku a
     * pôvodný dátum odhlásenia sa neprepisuje.
     */
    public function unsubscribe(Request $request, User $user)
    {
        $this->unsubscribeUser($user, $request->ip(), 'link');

        return $this->page($user->refresh());
    }

    public function resubscribe(Request $request, User $user)
    {
        $this->subscribeUser($user, 'link');

        return $this->page($user->refresh());
    }

    /** Nastavenie odberu v profile prihláseného používateľa. */
    public function edit(Request $request)
    {
        return view('newsletter.preferences', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['send_email' => ['required', 'boolean']]);
        $user = $request->user();

        $data['send_email']
            ? $this->subscribeUser($user, 'profile')
            : $this->unsubscribeUser($user, $request->ip(), 'profile');

        return redirect()->route('newsletter.preferences')
            ->with('flash', $data['send_email'] ? 'Odber noviniek je zapnutý.' : 'Odber noviniek je vypnutý.');
    }

    private function page(User $user)
    {
        return view('newsletter.unsubscribe', [
            'subscribed' => (bool) $user->send_email,
            'unsubscribedAt' => $user->newsletter_unsubscribed_at,
            'unsubscribeAction' => $this->signed('newsletter.unsubscribe', $user),
            'resubscribeAction' => $this->signed('newsletter.resubscribe', $user),
        ]);
    }

    private function signed(string $route, User $user): string
    {
        return \Illuminate\Support\Facades\URL::signedRoute($route, ['user' => $user->getKey()]);
    }

    private function unsubscribeUser(User $user, ?string $ip, string $source): void
    {
        if (! $user->send_email) {
            return;
        }

        $user->forceFill([
            'send_email' => false,
            'newsletter_unsubscribed_at' => now(),
            'newsletter_unsubscribed_ip' => $ip,
        ])->save();

        Recorder::info('newsletter', 'unsubscribed', 'Odhlásenie z newslettera',
            status: 'ok', userId: $user->id, ip: $ip, context: ['source' => $source]);
    }

    private function subscribeUser(User $user, string $source): void
    {
        if ($user->send_email) {
            return;
        }

        $user->forceFill([
            'send_email' => true,
            'newsletter_unsubscribed_at' => null,
            'newsletter_unsubscribed_ip' => null,
        ])->save();

        Recorder::info('newsletter', 'resubscribed', 'Obnovenie odberu newslettera',
            status: 'ok', userId: $user->id, ip: request()->ip(), context: ['source' => $source]);
    }
}
