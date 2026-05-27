<?php

namespace Lambdadigamma\LaravelApiLanguage;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lambdadigamma\LaravelApiLanguage\Data\LanguageNegotiationResult negotiate(\Illuminate\Http\Request|string|null $source, ?array $supportedLocales = null)
 * @method static string|null matchLocale(string $preferredLocale, array $availableLocales)
 * @method static bool isLocaleExcluded(string $locale, array $excludedLocales)
 * @method static bool isAcceptedLocaleExcluded(string $locale, array $excludedLocales)
 * @method static \Symfony\Component\HttpFoundation\Response addVaryHeader(\Symfony\Component\HttpFoundation\Response $response, string|array $headers = 'Accept-Language')
 *
 * @psalm-api
 *
 * @see \Lambdadigamma\LaravelApiLanguage\LanguageNegotiator
 */
class LaravelApiLanguageFacade extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return LanguageNegotiator::class;
    }
}
