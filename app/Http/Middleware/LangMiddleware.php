<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ChangeLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LangMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if(! Cookie::has('lang')) {
            return $next($request);
        }

        $lang = Cookie::get('lang') ?? 'en';

        (new ChangeLocale())->set($lang, isSetCookie: false);

        return $next($request);
    }
}