<?php

namespace App\Exceptions;

use App\Listeners\SystemLogSubscriber;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
        $this->reportable(function (TransportExceptionInterface $e) {
            SystemLogSubscriber::mailFailed($e);
        });
    }
}
