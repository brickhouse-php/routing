<?php

namespace Brickhouse\Routing;

/**
 * @phpstan-type            FoundRoute      array{0:mixed,1:array<string,mixed>}
 * @phpstan-import-type     StaticRoutes from RouteCollector
 * @phpstan-import-type     DynamicRoutes from RouteCollector
 */
class Dispatcher
{
    public function __construct(
        protected readonly RouteCollector $routeCollector
    ) {}

    /**
     * Undocumented function
     *
     * @param string $method
     * @param string $uri
     * @return null|FoundRoute
     */
    public function dispatch(string $method, string $uri): null|array
    {
        $staticRoutes = $this->routeCollector->static();
        if (isset($staticRoutes[$method][$uri])) {
            return [$staticRoutes[$method][$uri], []];
        }

        $dynamicRoutes = $this->routeCollector->dynamic();
        if (isset($dynamicRoutes[$method])) {
            $dispatchedRoute = $this->dispatchDynamic(
                $dynamicRoutes[$method],
                $method,
                $uri
            );

            if ($dispatchedRoute !== null) {
                return $dispatchedRoute;
            }
        }

        return null;
    }

    /**
     * Undocumented function
     *
     * @param array<string,Route>   $routes
     * @param string                $method
     * @param string                $uri
     *
     * @return null|FoundRoute
     */
    protected function dispatchDynamic(array $routes, string $method, string $uri): null|array
    {
        foreach ($routes as $pattern => $route) {
            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            $handler = $route->handler;

            $arguments = [];
            foreach (array_keys($route->arguments) as $argumentName) {
                $arguments[$argumentName] = $matches[$argumentName];
            }

            return [$handler, $arguments];
        }

        return null;
    }
}
