<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Odhlásenie z newslettera bez prihlásenia. Oprávnenie nesie podpis v URL
 * (middleware `signed` v routes/web.php), ktorý generuje App\Mail\PostNewsletter.
 */
class NewsletterController extends Controller
{
    public function show(Request $request, User $user)
    {
        return view('newsletter.unsubscribe', [
            'subscribed' => (bool) $user->send_email,
            'action' => $request->fullUrl(),
        ]);
    }

    public function unsubscribe(Request $request, User $user)
    {
        $user->update(['send_email' => false]);

        return view('newsletter.unsubscribe', [
            'subscribed' => false,
            'action' => $request->fullUrl(),
        ]);
    }
}
