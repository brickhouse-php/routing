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

    it('returns dynamic route with matching trailing slash', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id', '');
        $route = $dispatcher->dispatch('GET', '/users/1/');

        expect($route)->not->toBeNull();
    });

    it('returns dynamic route without matching trailing slash', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id/', '');
        $route = $dispatcher->dispatch('GET', '/users/1');

        expect($route)->not->toBeNull();
    });

    it('returns dynamic route with custom constraints', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id/', '', ['id' => '\d+']);
        $route = $dispatcher->dispatch('GET', '/users/1');

        expect($route)->not->toBeNull();
    });

    it('returns null given route with non-matching constraints', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('GET', '/users/:id/', '', ['id' => '\d+']);
        $route = $dispatcher->dispatch('GET', '/users/admin');

        expect($route)->toBeNull();
    });

    it('returns static route given route with wildcard method', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('*', '/', '');
        $route = $dispatcher->dispatch('POST', '/');

        expect($route)->not->toBeNull();
    });

    it('returns dynamic route given route with wildcard method', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('*', '/users/:id/', '');
        $route = $dispatcher->dispatch('GET', '/users/1');

        expect($route)->not->toBeNull();
    });

    it('returns all segments in wildcard argument', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('*', '/posts/*slug/', '');
        $route = $dispatcher->dispatch('GET', '/posts/2025/01/08/post-title');

        expect($route)->not->toBeNull();
        expect($route[1])->toMatchArray(['slug' => '2025/01/08/post-title']);
    });

    it('returns all segments in wildcard argument (ungreedy)', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);
        $dispatcher = new Dispatcher($collector);

        $collector->addRoute('*', '/posts/*slug/comments', '');
        $route = $dispatcher->dispatch('GET', '/posts/2025/01/08/post-title/comments');

        expect($route)->not->toBeNull();
        expect($route[1])->toMatchArray(['slug' => '2025/01/08/post-title']);
    });
})->group('dispatcher');
