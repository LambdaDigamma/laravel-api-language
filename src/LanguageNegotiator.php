<?php

namespace Lambdadigamma\LaravelApiLanguage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Lambdadigamma\LaravelApiLanguage\Data\LanguageNegotiationResult;
use Locale;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class LanguageNegotiator
{
    /**
     * @param  array<int, string>|null  $supportedLocales
     */
    public function negotiate(Request|string|null $source, ?array $supportedLocales = null): LanguageNegotiationResult
    {
        $acceptedLocales = [];
        $acceptLanguage = $this->acceptLanguageHeader($source);

        if ($source instanceof Request && $this->shouldPreferUserLocale()) {
            $userLocale = $this->userLocale($source);

            if ($userLocale !== null) {
                $acceptedLocales[] = $userLocale;
            }
        }

        [$headerAcceptedLocales, $excludedLocales] = $this->parseAcceptLanguage($acceptLanguage);
        $acceptedLocales = $this->filterAcceptedLocales(
            $this->uniqueLocales([...$acceptedLocales, ...$headerAcceptedLocales]),
            $excludedLocales
        );
        $supportedLocales = $this->supportedLocales($supportedLocales);
        [$resolvedLocale, $resolvedLocaleIsAcceptable] = $this->resolveLocale(
            $acceptedLocales,
            $excludedLocales,
            $supportedLocales
        );

        return new LanguageNegotiationResult(
            acceptedLocales: $acceptedLocales,
            excludedLocales: $excludedLocales,
            resolvedLocale: $resolvedLocale,
            resolvedLocaleIsAcceptable: $resolvedLocaleIsAcceptable,
        );
    }

    /**
     * @param  array<int, string>  $availableLocales
     */
    public function matchLocale(string $preferredLocale, array $availableLocales): ?string
    {
        $preferredLocale = $this->normalizeLocale($preferredLocale);
        $availableLocales = $this->uniqueLocales($availableLocales);

        if ($preferredLocale === '' || $availableLocales === []) {
            return null;
        }

        if ($preferredLocale === '*') {
            return $availableLocales[0];
        }

        foreach ($availableLocales as $availableLocale) {
            if (strtolower($availableLocale) === strtolower($preferredLocale)) {
                return $availableLocale;
            }
        }

        $match = Locale::lookup($availableLocales, $preferredLocale);

        if (is_string($match) && $match !== '') {
            return $match;
        }

        $preferredLanguage = $this->languagePart($preferredLocale);

        foreach ($availableLocales as $availableLocale) {
            if (strtolower($this->languagePart($availableLocale)) === strtolower($preferredLanguage)) {
                return $availableLocale;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $excludedLocales
     */
    public function isLocaleExcluded(string $locale, array $excludedLocales): bool
    {
        $locale = $this->normalizeLocale($locale);

        if ($locale === '') {
            return false;
        }

        foreach ($this->uniqueLocales($excludedLocales) as $excludedLocale) {
            if ($this->localeMatchesRange($locale, $excludedLocale)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $excludedLocales
     *
     * @psalm-api
     */
    public function isAcceptedLocaleExcluded(string $locale, array $excludedLocales): bool
    {
        return $this->isLocaleExcludedForAcceptedRange($locale, $excludedLocales);
    }

    /**
     * @param  string|array<int, string>  $headers
     */
    public function addVaryHeader(Response $response, string|array $headers = 'Accept-Language'): Response
    {
        $vary = collect(explode(',', (string) $response->headers->get('Vary')))
            ->map(fn (string $header): string => trim($header))
            ->filter()
            ->values();

        foreach ((array) $headers as $header) {
            if (! $vary->contains(fn (string $existing): bool => strtolower($existing) === strtolower($header))) {
                $vary->push($header);
            }
        }

        $response->headers->set('Vary', $vary->implode(', '));

        return $response;
    }

    /**
     * @param  array<int, string>|null  $supportedLocales
     * @return array<int, string>
     */
    public function supportedLocales(?array $supportedLocales = null): array
    {
        if ($supportedLocales !== null) {
            return $this->normalizeSupportedLocales($supportedLocales);
        }

        if ((bool) config('api-language.use_autoscan_lang_folder', false)) {
            return $this->normalizeSupportedLocales(Cache::rememberForever(
                (string) config('api-language.cache.supported_locales_key', 'api-language.supported-locales'),
                fn (): array => $this->scanLanguages(),
            ));
        }

        return $this->normalizeSupportedLocales(config('api-language.supported_locales', ['en']));
    }

    private function acceptLanguageHeader(Request|string|null $source): string
    {
        if ($source instanceof Request) {
            return (string) $source->headers->get('Accept-Language', '');
        }

        return (string) $source;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $acceptedLocales = [];
        $excludedLocales = [];

        if (trim($acceptLanguage) === '') {
            return [$acceptedLocales, $excludedLocales];
        }

        foreach (AcceptHeader::fromString($acceptLanguage)->all() as $item) {
            $locale = $this->normalizeLocale($item->getValue());

            if ($locale === '') {
                continue;
            }

            if ($item->getQuality() <= 0) {
                $excludedLocales[] = $locale;

                continue;
            }

            $acceptedLocales[] = $locale;
        }

        return [
            $this->uniqueLocales($acceptedLocales),
            $this->uniqueLocales($excludedLocales),
        ];
    }

    /**
     * @param  array<int, string>  $acceptedLocales
     * @param  array<int, string>  $excludedLocales
     * @param  array<int, string>  $supportedLocales
     * @return array{0: string, 1: bool}
     */
    private function resolveLocale(array $acceptedLocales, array $excludedLocales, array $supportedLocales): array
    {
        $fallbackLocale = $this->fallbackLocale();

        foreach ($acceptedLocales as $acceptedLocale) {
            if ($acceptedLocale === '*') {
                foreach ($supportedLocales as $supportedLocale) {
                    if (! $this->isLocaleExcluded($supportedLocale, $excludedLocales)) {
                        return [$supportedLocale, true];
                    }
                }

                continue;
            }

            $match = $this->matchLocale($acceptedLocale, $supportedLocales);

            if ($match !== null && ! $this->isLocaleExcludedForAcceptedRange($match, $excludedLocales)) {
                return [$match, true];
            }
        }

        $fallbackMatch = $this->matchLocale($fallbackLocale, $supportedLocales) ?? $fallbackLocale;

        if (! $this->isLocaleExcluded($fallbackMatch, $excludedLocales)) {
            return [$fallbackMatch, true];
        }

        foreach ($supportedLocales as $supportedLocale) {
            if (! $this->isLocaleExcluded($supportedLocale, $excludedLocales)) {
                return [$supportedLocale, true];
            }
        }

        return [$fallbackMatch, false];
    }

    private function fallbackLocale(): string
    {
        return $this->normalizeLocale(
            (string) (config('api-language.fallback_locale') ?? config('app.locale', 'en'))
        ) ?: 'en';
    }

    private function shouldPreferUserLocale(): bool
    {
        return (bool) config('api-language.prefer_user_locale', true);
    }

    private function userLocale(Request $request): ?string
    {
        $user = $request->user();
        $attribute = (string) config('api-language.user_locale_attribute', 'locale');

        if (! $user || $attribute === '') {
            return null;
        }

        $locale = data_get($user, $attribute);

        return is_string($locale) && $locale !== '' ? $this->normalizeLocale($locale) : null;
    }

    /**
     * @return array<int, string>
     */
    private function scanLanguages(): array
    {
        $path = resource_path('lang');

        if (! is_dir($path)) {
            return [];
        }

        return $this->uniqueLocales(array_values(array_diff(scandir($path) ?: [], ['..', '.'])));
    }

    private function normalizeLocale(string $locale): string
    {
        return trim(Str::replace('_', '-', $locale));
    }

    private function languagePart(string $locale): string
    {
        return explode('-', $locale, 2)[0];
    }

    /**
     * @param  array<int, string>  $acceptedLocales
     * @param  array<int, string>  $excludedLocales
     * @return array<int, string>
     */
    private function filterAcceptedLocales(array $acceptedLocales, array $excludedLocales): array
    {
        return collect($acceptedLocales)
            ->filter(fn (string $locale): bool => $locale === '*'
                ? ! in_array('*', $excludedLocales, true)
                : ! $this->isLocaleExcludedForAcceptedRange($locale, $excludedLocales))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $excludedLocales
     */
    private function isLocaleExcludedForAcceptedRange(string $locale, array $excludedLocales): bool
    {
        foreach ($this->uniqueLocales($excludedLocales) as $excludedLocale) {
            if ($excludedLocale === '*') {
                continue;
            }

            if ($this->localeMatchesRange($locale, $excludedLocale)) {
                return true;
            }
        }

        return false;
    }

    private function localeMatchesRange(string $locale, string $range): bool
    {
        $locale = strtolower($this->normalizeLocale($locale));
        $range = strtolower($this->normalizeLocale($range));

        if ($locale === '' || $range === '') {
            return false;
        }

        if ($range === '*') {
            return true;
        }

        if ($locale === $range) {
            return true;
        }

        return ! str_contains($range, '-')
            && strtolower($this->languagePart($locale)) === $range;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeSupportedLocales(mixed $locales): array
    {
        $supportedLocales = is_array($locales) ? $this->uniqueLocales($locales) : [];

        return $supportedLocales !== [] ? $supportedLocales : [$this->fallbackLocale()];
    }

    /**
     * @param  array<int, mixed>  $locales
     * @return array<int, string>
     */
    private function uniqueLocales(array $locales): array
    {
        $uniqueLocales = [];
        $seen = [];

        foreach ($locales as $locale) {
            if (! is_string($locale)) {
                continue;
            }

            $locale = $this->normalizeLocale($locale);

            if ($locale === '') {
                continue;
            }

            $key = strtolower($locale);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueLocales[] = $locale;
        }

        return $uniqueLocales;
    }
}
