<?php

namespace Brickhouse\Routing;

final readonly class Route
{
    public function __construct(
        public readonly string $method,
        public readonly mixed $handler,
        public readonly string $pattern,
        public readonly array $arguments,
    ) {}

    /**
     * Determines whether the given URI matches the route.
     *
     * @param string $uri
     *
     * @return boolean
     */
    public function match(string $uri): bool
    {
        return preg_match($this->pattern, $uri) !== false;
    }
}
