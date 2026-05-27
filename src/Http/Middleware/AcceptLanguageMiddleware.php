<?php

namespace Lambdadigamma\LaravelApiLanguage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lambdadigamma\LaravelApiLanguage\Data\LanguageNegotiationResult;
use Lambdadigamma\LaravelApiLanguage\LanguageNegotiator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class AcceptLanguageMiddleware
{
    public function __construct(
        private ?LanguageNegotiator $languageNegotiator = null,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $result = $this->languageNegotiator()->negotiate($request);

        app()->setLocale($result->resolvedLocale);
        $this->storeNegotiationResult($request, $result);

        $response = $next($request);

        if ((bool) config('api-language.automatic_vary_header', true) && $response instanceof Response) {
            $response = $this->languageNegotiator()->addVaryHeader($response);
        }

        return $response;
    }

    private function languageNegotiator(): LanguageNegotiator
    {
        return $this->languageNegotiator ??= app(LanguageNegotiator::class);
    }

    private function storeNegotiationResult(Request $request, LanguageNegotiationResult $result): void
    {
        $request->attributes->set(
            (string) config('api-language.request_attributes.accepted_locales', 'api_language.accepted_locales'),
            $result->acceptedLocales,
        );
        $request->attributes->set(
            (string) config('api-language.request_attributes.excluded_locales', 'api_language.excluded_locales'),
            $result->excludedLocales,
        );
        $request->attributes->set(
            (string) config('api-language.request_attributes.resolved_locale', 'api_language.resolved_locale'),
            $result->resolvedLocale,
        );
        $request->attributes->set(
            (string) config('api-language.request_attributes.result', 'api_language.result'),
            $result,
        );
    }
}
