<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Lambdadigamma\LaravelApiLanguage\Http\Middleware\ContentLanguageMiddleware;

/**
 * @var Response
 */
$response;

/**
 * @var ContentLanguageMiddleware
 */
$middleware;

beforeEach(function () {
    $this->response = new Response();
    $this->middleware = new ContentLanguageMiddleware();
});

test('content language header', function () {
    App::setLocale('de');

    $request = Request::create('/', 'GET', [], [], [], []);
    $middleware = new ContentLanguageMiddleware();
    $handled = $middleware->handle($request, function () {
        return $this->response;
    });

    expect($handled->headers->get('content-language'))->toBe('de');
    expect(App::getLocale())->toBe('de');
});

test('content language does not get set on binary response', function () {
    App::setLocale('de');
    Storage::fake();
    Storage::put('abc.txt', 'ABC');

    $request = Request::create('/', 'GET', [], [], [], []);
    $middleware = new ContentLanguageMiddleware();
    $handled = $middleware->handle($request, function () {
        return Storage::download('abc.txt');
    });

    expect(App::getLocale())->toBe('de');
    expect($handled->headers)->not->toBeNull();
});
