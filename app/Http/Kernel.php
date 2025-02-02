<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\canInstall;
use App\Http\Middleware\canUpdate;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfNotValid;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\TwoFactor;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
            CheckForMaintenanceMode::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
            'web' => [
                    EncryptCookies::class,
                    AddQueuedCookiesToResponse::class,
                    StartSession::class,
                // \Illuminate\Session\Middleware\AuthenticateSession::class,
                    ShareErrorsFromSession::class,
                    VerifyCsrfToken::class,
                    SubstituteBindings::class,
                    LocaleMiddleware::class,
            ],

            'api' => [
                    EnsureFrontendRequestsAreStateful::class,
                    SubstituteBindings::class,
                    ThrottleRequests::class.':api',
            ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
            'auth'          => Authenticate::class,
            'auth.basic'    => AuthenticateWithBasicAuth::class,
            'bindings'      => SubstituteBindings::class,
            'cache.headers' => SetCacheHeaders::class,
            'can'           => Authorize::class,
            'guest'         => RedirectIfAuthenticated::class,
            'signed'        => ValidateSignature::class,
            'throttle'      => ThrottleRequests::class,
            'verified'      => EnsureEmailIsVerified::class,
            'auth.session'     => AuthenticateSession::class,
            'password.confirm' => RequirePassword::class,

        /*
        |--------------------------------------------------------------------------
        | Swift SMS Based Middleware
        |--------------------------------------------------------------------------
        */

            'ValidProduct'  => RedirectIfNotValid::class,
            'twofactor'     => TwoFactor::class,
            'json.response' => ForceJsonResponse::class,
            'install'       => canInstall::class,
            'update'        => canUpdate::class,


    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
            StartSession::class,
            ShareErrorsFromSession::class,
            Authenticate::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
            Authorize::class,
    ];

}
