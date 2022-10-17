<?php

namespace Lambdadigamma\LaravelApiLanguage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ContentLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $contentLanguage = App::getLocale() ?? config('app.fallback_locale') ?? 'en';

        if (method_exists($response, 'header')) {
            $response->header('Content-Language', $contentLanguage);
        }

        return $response;
    }
}
