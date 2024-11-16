<?php
namespace Aireset\Http
{
    class Kernel extends \Illuminate\Foundation\Http\Kernel
    {
        protected $middleware = [
            'Aireset\Http\Middleware\VerifyInstallation',
            'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
            'Aireset\Http\Middleware\TrimStrings',
            'Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull',
            'Aireset\Http\Middleware\TrustProxies'
        ];
        protected $middlewareGroups = [
            'web' => [
                'Aireset\Http\Middleware\EncryptCookies',
                'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
                'Illuminate\Session\Middleware\StartSession',
                'Illuminate\View\Middleware\ShareErrorsFromSession',
                'Aireset\Http\Middleware\VerifyCsrfToken',
                'Illuminate\Routing\Middleware\SubstituteBindings',
                'Aireset\Http\Middleware\SelectLanguage'
            ],
            'api' => [
                'Aireset\Http\Middleware\UseApiGuard',
                'throttle:60,1',
                'bindings'
            ]
        ];
        protected $routeMiddleware = [
            'auth' => 'Aireset\Http\Middleware\Authenticate',
            'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
            'guest' => 'Aireset\Http\Middleware\RedirectIfAuthenticated',
            'registration' => 'Aireset\Http\Middleware\Registration',
            'session.database' => 'Aireset\Http\Middleware\DatabaseSession',
            'bindings' => 'Illuminate\Routing\Middleware\SubstituteBindings',
            'throttle' => 'Illuminate\Routing\Middleware\ThrottleRequests',
            'cache.headers' => 'Illuminate\Http\Middleware\SetCacheHeaders',
            'role' => 'jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyRole',
            'permission' => 'jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyPermission',
            'level' => 'jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyLevel',
            'ipcheck' => 'Aireset\Http\Middleware\IpMiddleware',
            'siteisclosed' => 'Aireset\Http\Middleware\SiteIsClosed',
            'localization' => 'Aireset\Http\Middleware\SelectLanguage',
            'shopzero' => 'Aireset\Http\Middleware\ShopZero'
        ];
    }

}
