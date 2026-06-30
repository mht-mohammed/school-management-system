<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->query('lang', $request->cookie('lang', session('lang', config('app.locale'))));

        if (in_array($lang, ['ar', 'en'])) {
            app()->setLocale($lang);
            session(['lang' => $lang]);
            cookie()->queue('lang', $lang, 60 * 24 * 365);
        }

        return $next($request);
    }
}
