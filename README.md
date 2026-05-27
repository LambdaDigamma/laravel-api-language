# A simple package for making a Laravel API language header aware.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lambdadigamma/laravel-api-language.svg?style=flat-square)](https://packagist.org/packages/lambdadigamma/laravel-api-language)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/lambdadigamma/laravel-api-language/run-tests?label=tests)](https://github.com/lambdadigamma/laravel-api-language/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/lambdadigamma/laravel-api-language/Check%20&%20fix%20styling?label=code%20style)](https://github.com/lambdadigamma/laravel-api-language/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lambdadigamma/laravel-api-language.svg?style=flat-square)](https://packagist.org/packages/lambdadigamma/laravel-api-language)

---

## Installation

You can install the package via composer:

```bash
composer require lambdadigamma/laravel-api-language
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Lambdadigamma\LaravelApiLanguage\LaravelApiLanguageServiceProvider" --tag="api-language-config"
```

This is the contents of the published config file:

```php
return [

    'supported_locales' => ['en'],

    'fallback_locale' => null,

    'use_autoscan_lang_folder' => false,

    'cache' => [
        'supported_locales_key' => 'api-language.supported-locales',
    ],

    'request_attributes' => [
        'accepted_locales' => 'api_language.accepted_locales',
        'excluded_locales' => 'api_language.excluded_locales',
        'resolved_locale' => 'api_language.resolved_locale',
        'result' => 'api_language.result',
    ],

    'prefer_user_locale' => true,

    'user_locale_attribute' => 'locale',

    'automatic_vary_header' => true,

];
```

## Usage

To use the accept language middleware, register it with your application's middleware.

In Laravel 11 and newer, append it to the API middleware group in `bootstrap/app.php`:

```php
use Illuminate\Foundation\Configuration\Middleware;
use Lambdadigamma\LaravelApiLanguage\Http\Middleware\AcceptLanguageMiddleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        AcceptLanguageMiddleware::class,
    ]);
})
```

In older Laravel applications, register it as a global middleware:

```php
protected $middleware = [
    ...
    \Lambdadigamma\LaravelApiLanguage\Http\Middleware\AcceptLanguageMiddleware::class,
];
```

Register in a specific middleware group:

```php
protected $middlewareGroups = [
    'api' => [
        ...
        \Lambdadigamma\LaravelApiLanguage\Http\Middleware\AcceptLanguageMiddleware::class,
    ]
];
```

The middleware stores the full negotiation result on the request. This is useful
for APIs that need to pick resource-specific translations instead of only setting
Laravel's application locale:

```php
$acceptedLocales = $request->attributes->get('api_language.accepted_locales', []);
$excludedLocales = $request->attributes->get('api_language.excluded_locales', []);
$resolvedLocale = $request->attributes->get('api_language.resolved_locale');
```

You can also use the negotiator directly:

```php
use Lambdadigamma\LaravelApiLanguage\LanguageNegotiator;

$result = app(LanguageNegotiator::class)->negotiate($request);

$result->acceptedLocales; // ordered positive locales, including positive * and excluding q=0
$result->excludedLocales; // q=0 locales, including *
$result->resolvedLocale;  // best supported application locale
$result->resolvedLocaleIsAcceptable; // false when every supported locale is excluded
```

Use `isAcceptedLocaleExcluded()` when checking an explicit positive locale from
`acceptedLocales`; it honors concrete `q=0` ranges without letting `*;q=0`
reject explicitly accepted languages.

`AcceptLanguageMiddleware` adds `Vary: Accept-Language` by default. Disable this
with `automatic_vary_header` if another layer owns response cache variation.
When `prefer_user_locale` is enabled for authenticated APIs, make sure those
responses are private/auth-aware in your cache layer because the resolved locale
can also depend on the current user.

`ContentLanguageMiddleware` is available for single-locale responses. It will not
overwrite an existing `Content-Language` header.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Lennart Fischer](https://github.com/LambdaDigamma)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
