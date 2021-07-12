<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Lambdadigamma\LaravelApiLanguage\Http\Middleware\AcceptLanguageMiddleware;

/**
 * @var Response
 */
$response;

/**
 * @var AcceptLanguageMiddleware
 */
$middleware;

beforeEach(function () {
    $this->response = new Response();
    $this->middleware = new AcceptLanguageMiddleware();
});

it('can parse locale tag', function () {
    
    expect(App::getLocale())->toBe('en');
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de',
    ]);

    $this->middleware->handle($request, function () {
        return $this->response;
    });

    expect(App::getLocale())->toBe('de');
});

it('can parse full qualified locale', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de-DE',
    ]);
    expect(App::getLocale())->toBe('en');

    $this->middleware->handle($request, function () {
        return $this->response;
    });

    $this->assertEquals('de', App::getLocale());
});

it('can parse qualified locale with quality value', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de-DE;q=0.5',
    ]);
    expect(App::getLocale())->toBe('en');

    $this->middleware->handle($request, function () {
        return $this->response;
    });

    expect(App::getLocale())->toBe('de');
});

it('can parse multiple locales', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de, en',
    ]);
    expect(App::getLocale())->toBe('en');

    $this->middleware->handle($request, function () {
        return $this->response;
    });

    $this->assertEquals('de', App::getLocale());
});

it('can parse multiple qualified locale tags', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'es-US, de-DE',
    ]);
    expect(App::getLocale())->toBe('en');

    $this->middleware->handle($request, function () {
        return $this->response;
    });

    expect(App::getLocale())->toBe('es');
    // expect(App::getLocale())->toBe('es-US');
});