<?php

namespace Lambdadigamma\LaravelApiLanguage\Tests;

use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;
use Lambdadigamma\LaravelApiLanguage\LaravelApiLanguageServiceProvider;

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
