<?php

namespace App\Exceptions;

use App\Listeners\SystemLogSubscriber;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Mail, ktorý neodišiel, patrí aj do denníka udalostí (admin → Denník),
        // nielen do súborového logu. Hlásenie sa tým nezastaví.
        // ModelNotFoundException sa mení na NotFoundHttpException s pôvodnou
        // správou ("No query results for model [App\Models\Post] 1"), ktorú
        // by klient videl aj pri APP_DEBUG=false.
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nenájdené.'], 404);
            }
        });

        $this->reportable(function (TransportExceptionInterface $e) {
            SystemLogSubscriber::mailFailed($e);
        });

        // Password::uncompromised() pri nedostupnom api.pwnedpasswords.com
        // heslo prepustí a výnimku len nahlási — výpadok DNS/siete servera tak
        // plnil log ERRORmi s celým stack trace. Stačí krátke varovanie.
        $this->reportable(function (ConnectionException $e) {
            if (str_contains($e->getMessage(), 'api.pwnedpasswords.com')) {
                Log::warning('Kontrola uniknutých hesiel nedostupná, heslo prepustené: '.$e->getMessage());

                return false;
            }
        });
    }
}
