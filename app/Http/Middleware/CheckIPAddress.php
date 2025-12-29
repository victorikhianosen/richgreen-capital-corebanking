<?php

namespace App\Http\Middleware;

use App\Models\Ipwhitelisting;
use Closure;
use Illuminate\Http\Request;

class CheckIPAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $chkIp = Ipwhitelisting::all()->toArray();
        
        if (!in_array($request->getClientIp(), $chkIp['ip_address'])) {
            return response()->json(['status' => false, 'message' => 'You are restricted from Accessing this site'], 403);
        }
        return $next($request);
    }
}
