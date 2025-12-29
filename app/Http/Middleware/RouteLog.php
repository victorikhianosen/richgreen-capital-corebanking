<?php

namespace App\Http\Middleware;

use App\Http\Traites\UserTraite;
use Closure;
use Illuminate\Http\Request;

class RouteLog
{
    use UserTraite;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        global $response;

        if (app()->environment('local') || app()->environment('production')) {
            // $log = [
            //     'URI' => $request->getUri(),
            //     'METHOD' => $request->getMethod(),
            //     'REQUEST_BODY' => $request->all(),
            //      'RESPONSE' => $response->getContent()
            // ];

            $this->logInfo("", $request->getUri());
            
            $response = $next($request);
            
        }

        return $response;
    }
}
