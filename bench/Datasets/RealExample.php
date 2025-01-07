<?php

namespace Brickhouse\Routing\Benchmarks\Datasets;

use Brickhouse\Routing\Dispatcher;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;

class RealExample
{
    public const array ROUTES = [
        ['GET', '/'],
        ['GET', '/page/*slug'],
        ['GET', '/about-us'],
        ['GET', '/contact-us'],
        ['POST', '/contact-us'],
        ['GET', '/blog'],
        ['GET', '/blog/recent'],
        ['GET', '/blog/post/:slug'],
        ['POST', '/blog/post/:slug/comment'],
        ['GET', '/shop'],
        ['GET', '/shop/category'],
        ['GET', '/shop/category/search/:filter'],
        ['GET', '/shop/category/:category_id'],
        ['GET', '/shop/category/:category_id/product'],
        ['GET', '/shop/category/:category_id/product/search/:filter'],
        ['GET', '/shop/product'],
        ['GET', '/shop/product/search/:filter'],
        ['GET', '/shop/product/:product_id'],
        ['GET', '/shop/cart'],
        ['PUT', '/shop/cart'],
        ['DELETE', '/shop/cart'],
        ['GET', '/shop/cart/checkout'],
        ['POST', '/shop/cart/checkout'],
        ['GET', '/admin/login'],
        ['POST', '/admin/login'],
        ['GET', '/admin/logout'],
        ['GET', '/admin'],
        ['GET', '/admin/product'],
        ['GET', '/admin/product/create'],
        ['POST', '/admin/product'],
        ['GET', '/admin/product/:product_id'],
        ['GET', '/admin/product/:product_id/edit'],
        [['PUT', 'PATCH'], '/admin/product/:product_id'],
        ['DELETE', '/admin/product/:product_id'],
        ['GET', '/admin/category'],
        ['GET', '/admin/category/create'],
        ['POST', '/admin/category'],
        ['GET', '/admin/category/category_id'],
        ['GET', '/admin/category/category_id/edit'],
        [['PUT', 'PATCH'], '/admin/category/category_id'],
        ['DELETE', '/admin/category/category_id'],
    ];

    public static function parse(RouteParser $parser): void
    {
        foreach (self::ROUTES as [, $route]) {
            $parser->parse($route);
        }
    }

    public static function register(RouteCollector $collector): void
    {
        foreach (RealExample::ROUTES as [$methods, $route]) {
            $collector->addRoute($methods, $route, '');
        }
    }

    public static function dispatch(Dispatcher $dispatcher): void
    {
        foreach (RealExample::ROUTES as [$methods, $route]) {
            $dispatcher->dispatch(((array) $methods)[0], $route);
        }
    }
}
