<?php

use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;

function patternData(string $pattern): array
{
    $parser = new RouteParser;
    $collector = new RouteCollector($parser);

    $collector->addRoute('GET', $pattern, 'route');

    return ['static' => $collector->static(), 'dynamic' => $collector->dynamic()];
}

expect()->extend('toHaveNoStatics', function () {
    $this->static->toBeEmpty();

    return $this;
});

expect()->extend('toHaveStatic', function (string $route) {
    $this->static->each->toHaveKey($route);

    return $this;
});

expect()->extend('toHaveNoDynamics', function () {
    $this->dynamic->toBeEmpty();

    return $this;
});

expect()->extend('toHaveDynamic', function (string $route) {
    $this->dynamic->each->toHaveKey($route);

    return $this;
});

describe('RouteCollector', function () {
    it('creates static pattern given static route')
        ->expect(fn() => patternData("/static"))
        ->toHaveNoDynamics()
        ->toHaveStatic('/static');

    it('creates dynamic pattern given route with single argument')
        ->expect(fn() => patternData("/dynamic/:id"))
        ->toHaveNoStatics()
        ->toHaveDynamic('~^/dynamic/(?<id>[^/]+)$~');
});
