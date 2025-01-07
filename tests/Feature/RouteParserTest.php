<?php

use Brickhouse\Routing\RouteParser;

function parse(string $route): array
{
    return new RouteParser()->parse($route);
}

describe('RouteParser', function () {
    it('yields static route given static route')
        ->expect(fn() => parse("/static"))
        ->toEqual(['/static']);

    it('yields expanded argument')
        ->expect(fn() => parse("/:param"))
        ->toEqual(['/', ['param' => '[^/]+']]);

    it('yields expanded wildcard argument')
        ->expect(fn() => parse("/*param"))
        ->toEqual(['/', ['param' => '.*']]);

    it('yields multiple expanded arguments')
        ->expect(fn() => parse("/:param1/:param2"))
        ->toEqual(['/', ['param1' => '[^/]+'], '/', ['param2' => '[^/]+']]);

    it('yields expanded arguments with statics')
        ->expect(fn() => parse("/user/:id/items/:item"))
        ->toEqual(['/user/', ['id' => '[^/]+'], '/items/', ['item' => '[^/]+']]);

    it('allows underscores in argument names')
        ->expect(fn() => parse("/:user_id"))
        ->toEqual(['/', ['user_id' => '[^/]+']]);

    it('allows hyphens in argument names')
        ->expect(fn() => parse("/:user-id"))
        ->toEqual(['/', ['user-id' => '[^/]+']]);
});
