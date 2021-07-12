<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
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