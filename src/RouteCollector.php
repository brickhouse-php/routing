<?php

namespace Brickhouse\Routing;

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
     * @param string|list<string>   $methods    HTTP method(s) which the route responds to.
     * @param string                $route      Pattern of the route.
     * @param mixed                 $handler    Callback for when the route is dispatched.
     *
     * @return void
     */
    public function addRoute(string|array $methods, string $route, mixed $handler): void
    {
        $routeData = $this->routeParser->parse($route);

        if (is_string($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            if ($this->isRouteStatic($routeData)) {
                $this->addStaticRoute($method, $routeData, $handler);
            } else {
                $this->addDynamicRoute($method, $routeData, $handler);
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
     * @param string    $method
     * @param array     $routeData
     * @param mixed     $handler
     *
     * @return void
     */
    protected function addDynamicRoute(string $method, array $routeData, mixed $handler): void
    {
        [$pattern, $arguments] = $this->buildRoutePattern($routeData);

        $this->dynamicRoutes[$method] ??= [];
        $this->dynamicRoutes[$method][$pattern] = new Route($method, $handler, $pattern, $arguments);
    }

    /**
     * Builds a regex pattern for the given route.
     *
     * @param list<string|array<string,string>>     $routeData
     *
     * @return array{0:string,1:array<string,bool>}
     */
    protected function buildRoutePattern(array $routeData): array
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
            $argumentPattern = current($part);

            if (isset($arguments[$argumentName])) {
                throw new \RuntimeException(
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
