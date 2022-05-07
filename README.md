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

    'use_autoscan_lang_folder' => false

];
```

## Usage

To use the accept language middleware, just register it in your application's HTTP kernel.

Register it as a global middleware:

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
