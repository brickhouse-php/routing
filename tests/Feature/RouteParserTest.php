<?php

use Brickhouse\Routing\RouteParser;

function parse(string $route): array
{
    return new RouteParser()->parse($route);
}

describe('RouteParser', function () {
    it('yields static route given static route')
        ->expect(fn() => parse("/static"))
        ->toEqual([['/static']]);

    it('yields expanded argument')
        ->expect(fn() => parse("/:param"))
        ->toEqual([['/', ['param' => '[^/]+']]]);

    it('yields expanded wildcard argument')
        ->expect(fn() => parse("/*param"))
        ->toEqual([['/', ['param' => '.*']]]);

    it('yields multiple expanded arguments')
        ->expect(fn() => parse("/:param1/:param2"))
        ->toEqual([['/', ['param1' => '[^/]+'], '/', ['param2' => '[^/]+']]]);

    it('yields expanded arguments with statics')
        ->expect(fn() => parse("/user/:id/items/:item"))
        ->toEqual([['/user/', ['id' => '[^/]+'], '/items/', ['item' => '[^/]+']]]);

    it('allows underscores in argument names')
        ->expect(fn() => parse("/:user_id"))
        ->toEqual([['/', ['user_id' => '[^/]+']]]);

    it('allows hyphens in argument names')
        ->expect(fn() => parse("/:user-id"))
        ->toEqual([['/', ['user-id' => '[^/]+']]]);

    it('allows routes after arguments')
        ->expect(fn() => parse("/:user-id/posts"))
        ->toEqual([['/', ['user-id' => '[^/]+'], '/posts']]);

    it('yields available routes given optional argument')
        ->expect(fn() => parse("/:?slug"))
        ->toEqual([
            ['/'],
            ['/', ['slug' => '[^/]+']]
        ]);

    it('yields available routes given multiple optional arguments')
        ->expect(fn() => parse("/fixed/:var1/:?var2"))
        ->toEqual([
            ['/fixed/', ['var1' => '[^/]+'], '/'],
            ['/fixed/', ['var1' => '[^/]+'], '/', ['var2' => '[^/]+']]
        ]);

    it('yields available routes given optional argument before statics')
        ->expect(fn() => parse("/posts/:?slug/view"))
        ->toEqual([
            ['/posts/view'],
            ['/posts/', ['slug' => '[^/]+'], '/view'],
        ]);

    it('allows routes after optional arguments')
        ->expect(fn() => parse("/:?user-id/posts"))
        ->toEqual([
            ['/posts'],
            ['/', ['user-id' => '[^/]+'], '/posts'],
        ]);
});
