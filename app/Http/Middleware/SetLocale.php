<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'de'];

    public function handle(Request $request, Closure $next): Response
    {
        $preferred = $request->getPreferredLanguage(self::SUPPORTED);
        App::setLocale($preferred ?? 'en');

        return $next($request);
    }
}
