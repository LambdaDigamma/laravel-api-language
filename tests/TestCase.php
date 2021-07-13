<?php

namespace Lambdadigamma\LaravelApiLanguage\Tests;

use Illuminate\Support\Facades\Config;
use Lambdadigamma\LaravelApiLanguage\LaravelApiLanguageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelApiLanguageServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        Config::set('api-language.supported_locales', ['en', 'de', 'es']);
    }
}
