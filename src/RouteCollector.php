<?php

namespace Brickhouse\Routing;

use Brickhouse\Routing\Exceptions\RouteArgumentException;

/**
 * @phpstan-type    StaticRoutes    array<string,array<string,mixed>>
 * @phpstan-type    DynamicRoutes   array<string,array<string,Route>>
 */
class RouteCollector
{
    /**
     * Gets all the static routes in the collector.
     *
     * @var StaticRoutes
     */
    protected array $staticRoutes = [];

    /**
     * Gets all the dynamic routes in the collector.
     *
     * @var DynamicRoutes
     */
    protected array $dynamicRoutes = [];

    public function __construct(
        protected readonly RouteParser $routeParser,
    ) {}

    /**
     * Gets all the static routes on the collector.
     *
     * @return StaticRoutes
     */
    public function &static(): array
    {
        return $this->staticRoutes;
    }

    /**
     * Gets all the dynamic routes on the collector.
     *
     * @return DynamicRoutes
     */
    public function &dynamic(): array
    {
        return $this->dynamicRoutes;
    }

    /**
     * Adds a route to the collector.
     *
     * @param string|list<string>   $methods        HTTP method(s) which the route responds to.
     * @param string                $route          Pattern of the route.
     * @param mixed                 $handler        Callback for when the route is dispatched.
     * @param array<string,string>  $constraints    Array of custom constraints.
     *
     * @return void
     */
    public function addRoute(string|array $methods, string $route, mixed $handler, array $constraints = []): void
    {
        // Remove trailing slashes
        if ($route !== '/' && str_ends_with($route, '/')) {
            $route = rtrim($route, '/');
        }

        $routeTemplates = $this->routeParser->parse($route);

        if (is_string($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            foreach ($routeTemplates as $routeTemplate) {
                if ($this->isRouteStatic($routeTemplate)) {
                    $this->addStaticRoute($method, $routeTemplate, $handler);
                } else {
                    $this->addDynamicRoute($method, $routeTemplate, $handler, $constraints);
                }
            }
        }
    }

    /**
     * Determines whether the given parsed route data is of a static route.
     *
     * @param array $routeData
     *
     * @return boolean
     */
    protected function isRouteStatic(array $routeData): bool
    {
        return count($routeData) === 1 && is_string($routeData[0]);
    }

    /**
     * Adds the given static route to the collector.
     *
     * @param string    $method
     * @param array     $routeData
     * @param mixed     $handler
     *
     * @return void
     */
    protected function addStaticRoute(string $method, array $routeData, mixed $handler): void
    {
        $route = $routeData[0];

        $this->staticRoutes[$method] ??= [];
        $this->staticRoutes[$method][$route] = $handler;
    }

    /**
     * Adds the given dynamic route to the collector.
     *
     * @param string                $method
     * @param array                 $routeData
     * @param mixed                 $handler
     * @param array<string,string>  $constraints
     *
     * @return void
     */
    protected function addDynamicRoute(string $method, array $routeData, mixed $handler, array $constraints): void
    {
        [$pattern, $arguments] = $this->buildRoutePattern($routeData, $constraints);

        $this->dynamicRoutes[$method] ??= [];
        $this->dynamicRoutes[$method][$pattern] = new Route($method, $handler, $pattern, $arguments);
    }

    /**
     * Builds a regex pattern for the given route.
     *
     * @param list<string|array<string,string>>     $routeData
     * @param array<string,string>                  $constraints
     *
     * @return array{0:string,1:array<string,bool>}
     */
    protected function buildRoutePattern(array $routeData, array $constraints): array
    {
        $regex = '';
        $arguments = [];

        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            /** @var string $argumentName */
            $argumentName = key($part);

            /** @var string $argumentPattern */
            $argumentPattern = $constraints[$argumentName] ?? current($part);

            if (isset($arguments[$argumentName])) {
                throw new RouteArgumentException(
                    "Cannot use argument name '{$argumentName}' twice."
                );
            }

            $arguments[$argumentName] = true;
            $regex .= "(?<{$argumentName}>" . $argumentPattern . ')';
        }

        $regex = '~^' . $regex . '$~';

        return [$regex, $arguments];
    }
}
