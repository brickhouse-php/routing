<?php

use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;

describe('RouteCollector', function () {
    it('throws exception given duplicate argument names', function () {
        $parser = new RouteParser;
        $collector = new RouteCollector($parser);

        $collector->addRoute('GET', '/users/:id/photos/:id', 'route');
    })->throws(\RuntimeException::class);
});
