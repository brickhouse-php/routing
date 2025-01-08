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
}
