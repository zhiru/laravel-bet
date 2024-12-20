<?php
namespace Aireset\Http\Middleware
{
    class SiteIsClosed
    {
        public function handle($request, \Closure $next)
        {
            if( auth()->check() && auth()->user()->role_id == 6 )
            {
                return $next($request);
            }
            if( setting('siteisclosed') )
            {
                return response()->view('system.pages.siteisclosed', [], 200)->header('Content-Type', 'text/html');
            }
            return $next($request);
        }
    }

}
