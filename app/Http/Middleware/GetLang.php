<?php

namespace App\Http\Middleware;

use Closure;

class GetLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app()->setLocale('en');
        if($request->hasHeader("lang") && $request->header("lang") == 'ar'){
            app()->setLocale('ar');
        }
        return $next($request);
    }
}
