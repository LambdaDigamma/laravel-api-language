<?php

namespace Lambdadigamma\LaravelApiLanguage\Data;

readonly class LanguageNegotiationResult
{
    /**
     * @param  array<int, string>  $acceptedLocales
     * @param  array<int, string>  $excludedLocales
     */
    public function __construct(
        public array $acceptedLocales,
        public array $excludedLocales,
        public string $resolvedLocale,
        public bool $resolvedLocaleIsAcceptable = true,
    ) {
    }
}
