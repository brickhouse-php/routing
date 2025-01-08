<?php

use Brickhouse\Routing\Dispatcher;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;

function dispatcher(string $route): array
{
    return new RouteParser()->parse($route);
}

describe('Dispatcher', function () {
    it('returns null without any routes', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $route = $dispatcher->dispatch('GET', '/');

        expect($route)->toBeNull();
    });

    it('returns static route', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/', fn() => 'Hello World!');

        $route = $dispatcher->dispatch('GET', '/');

        expect($route)->not->toBeNull();
    });

    it('returns dynamic route', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id', fn() => 'Hello, Max!');
        $route = $dispatcher->dispatch('GET', '/users/1');

        expect($route)->not->toBeNull();
        expect($route[0]())->toBe('Hello, Max!');
        expect($route[1])->toMatchArray(['id' => 1]);
    });

    it('returns complex type handler', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id', (object)['1' => 'foo']);
        $route = $dispatcher->dispatch('GET', '/users/1');

        expect($route)->not->toBeNull();
        expect($route[0])->toBeObject();
    });

    it('returns complex dynamic route', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:user_id/photos/:photo_id', '');
        $route = $dispatcher->dispatch('GET', '/users/2/photos/4');

        expect($route)->not->toBeNull();
        expect($route[1])->toMatchArray(['user_id' => 2, 'photo_id' => 4]);
    });

    it('returns dynamic route with dot as argument', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:user_id/photos/:path', '');
        $route = $dispatcher->dispatch('GET', '/users/2/photos/latest.jpg');

        expect($route)->not->toBeNull();
        expect($route[1])->toMatchArray(['user_id' => 2, 'path' => 'latest.jpg']);
    });

    it('returns null given mismatching dynamic route', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:user_id/photos/:path', '');
        $route = $dispatcher->dispatch('GET', '/users/2/photos/');

        expect($route)->toBeNull();
    });
})->group('dispatcher');
