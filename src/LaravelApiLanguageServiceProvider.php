<?php

namespace Lambdadigamma\LaravelApiLanguage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * @psalm-api
 */
class LaravelApiLanguageServiceProvider extends PackageServiceProvider
{
    #[\Override]
    public function packageRegistered(): void
    {
        $this->app->singleton(LanguageNegotiator::class);
    }

    #[\Override]
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-language')
            ->hasConfigFile()
            ->hasViews();
    }
}
