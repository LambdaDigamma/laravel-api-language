<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Lambdadigamma\LaravelApiLanguage\LanguageNegotiator;

beforeEach(function () {
    $this->languageNegotiator = new LanguageNegotiator();
});

it('excludes q zero locales from accepted locales', function () {
    $result = $this->languageNegotiator->negotiate('de;q=0, en;q=0.8', ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['en']);
    expect($result->excludedLocales)->toBe(['de']);
    expect($result->resolvedLocale)->toBe('en');
});

it('does not resolve to an explicitly excluded fallback locale when another supported locale exists', function () {
    $result = $this->languageNegotiator->negotiate('en;q=0', ['en', 'de']);

    expect($result->acceptedLocales)->toBe([]);
    expect($result->excludedLocales)->toBe(['en']);
    expect($result->resolvedLocale)->toBe('de');
});

it('preserves quality order and normalizes underscore locale separators', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de_DE,de;q=0.9,en;q=0.8',
    ]);

    $result = $this->languageNegotiator->negotiate($request, ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['de-DE', 'de', 'en']);
    expect($result->excludedLocales)->toBe([]);
    expect($result->resolvedLocale)->toBe('de');
});

it('supports wildcard exclusions', function () {
    $result = $this->languageNegotiator->negotiate('*;q=0', ['en', 'de']);

    expect($result->acceptedLocales)->toBe([]);
    expect($result->excludedLocales)->toBe(['*']);
    expect($result->resolvedLocale)->toBe('en');
    expect($result->resolvedLocaleIsAcceptable)->toBeFalse();
    expect($this->languageNegotiator->isLocaleExcluded('en', $result->excludedLocales))->toBeTrue();
    expect($this->languageNegotiator->isLocaleExcluded('de', $result->excludedLocales))->toBeTrue();
});

it('does not let a regional q zero exclusion reject the base locale', function () {
    $result = $this->languageNegotiator->negotiate('de-DE;q=0, de;q=0.8', ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['de']);
    expect($result->excludedLocales)->toBe(['de-DE']);
    expect($result->resolvedLocale)->toBe('de');
    expect($result->resolvedLocaleIsAcceptable)->toBeTrue();
    expect($this->languageNegotiator->isLocaleExcluded('de', ['de-DE']))->toBeFalse();
    expect($this->languageNegotiator->isLocaleExcluded('de-DE', ['de-DE']))->toBeTrue();
});

it('lets a base q zero exclusion reject regional locales', function () {
    $result = $this->languageNegotiator->negotiate('de;q=0, de-DE;q=0.8', ['en', 'de']);

    expect($result->acceptedLocales)->toBe([]);
    expect($result->excludedLocales)->toBe(['de']);
    expect($result->resolvedLocale)->toBe('en');
    expect($this->languageNegotiator->isLocaleExcluded('de-DE', ['de']))->toBeTrue();
});

it('honors wildcard quality before explicit lower quality locales', function () {
    $result = $this->languageNegotiator->negotiate('*;q=0.9, de;q=0.8', ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['*', 'de']);
    expect($result->excludedLocales)->toBe([]);
    expect($result->resolvedLocale)->toBe('en');
});

it('honors explicit quality before lower quality wildcard', function () {
    $result = $this->languageNegotiator->negotiate('de;q=0.9, *;q=0.8', ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['de', '*']);
    expect($result->excludedLocales)->toBe([]);
    expect($result->resolvedLocale)->toBe('de');
});

it('keeps wildcard q zero out of accepted resource locales', function () {
    $result = $this->languageNegotiator->negotiate('de;q=1, *;q=0', ['en', 'de']);

    expect($result->acceptedLocales)->toBe(['de']);
    expect($result->excludedLocales)->toBe(['*']);
    expect($result->resolvedLocale)->toBe('de');
    expect($result->resolvedLocaleIsAcceptable)->toBeTrue();
    expect($this->languageNegotiator->isAcceptedLocaleExcluded('de', ['*']))->toBeFalse();
});

it('falls back when configured supported locales are invalid or empty', function () {
    Config::set('api-language.fallback_locale', 'de');

    Config::set('api-language.supported_locales', 'en');
    expect($this->languageNegotiator->supportedLocales())->toBe(['de']);

    Config::set('api-language.supported_locales', null);
    expect($this->languageNegotiator->supportedLocales())->toBe(['de']);

    Config::set('api-language.supported_locales', []);
    expect($this->languageNegotiator->supportedLocales())->toBe(['de']);
});

it('matches regional locales to supported base locales', function () {
    expect($this->languageNegotiator->matchLocale('de-DE', ['en', 'de']))->toBe('de');
});

it('merges vary headers without duplicates', function () {
    $response = new Response();
    $response->headers->set('Vary', 'Accept-Encoding, Accept-Language');

    $this->languageNegotiator->addVaryHeader($response, ['Accept-Language', 'X-Debug']);

    expect($response->headers->get('Vary'))->toBe('Accept-Encoding, Accept-Language, X-Debug');
});
