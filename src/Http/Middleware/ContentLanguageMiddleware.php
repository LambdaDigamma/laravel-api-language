<?php

namespace Lambdadigamma\LaravelApiLanguage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ContentLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $contentLanguage = App::getLocale() ?? config('app.fallback_locale') ?? 'en';

        if ($response instanceof Response && ! $response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', $contentLanguage);
        }

        return $response;
    }
}
