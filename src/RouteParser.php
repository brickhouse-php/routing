<?php

namespace Brickhouse\Routing;

use Brickhouse\Routing\Exceptions\RouteArgumentException;

class RouteParser
{
    protected const string ROUTE_PATTERN = "/\/((?<quantifier>[\*:])(?<optional>\?)?(?<name>[\w-]+))/";

    /**
     * Parse the given route into an array of statics and arguments.
     *
     * @param string $route
     *
     * @return array
     */
    public function parse(string $route): array
    {
        // Add trailing slashes
        if (!str_ends_with($route, '/')) {
            $route .= '/';
        }

        if (!preg_match_all(self::ROUTE_PATTERN, $route, $matches, PREG_OFFSET_CAPTURE)) {
            return [[$route]];
        }

        $quantifierPatterns = [
            ':' => '[^/]+',
            '*' => '.*',
        ];

        $routes = [];
        $offset = 0;

        for ($i = 0; isset($matches[0][$i]); $i++) {
            [$match, $position] = $matches[1][$i];
            [$quantifier] = $matches['quantifier'][$i];
            [$optional] = $matches['optional'][$i];
            [$name] = $matches['name'][$i];

            if (str_contains($name, '-')) {
                throw new RouteArgumentException(
                    "Cannot use hyphens (-) in argument name ({$name})."
                );
            }

            $optional = strlen($optional) > 0;
            $quantifierPattern = $quantifierPatterns[$quantifier];

            // Add the static part to the first route slot.
            // The first route slot is the only slot without any optional parts.
            $routes[0][] = substr($route, $offset, $position - $offset);

            // If the segment isn't optional, we can add the dynamic part, as well.
            if (!$optional) {
                $routes[0][] = [$name => $quantifierPattern];
            } else {
                // If the segment is optional, copy the static parts from the first slot
                // and add the dynamic part.
                $routes[] = [
                    ...$routes[0],
                    [$name => $quantifierPattern]
                ];
            }

            $offset = $position + strlen($match);
        }

        // Add any remaining statics.
        foreach (array_keys($routes) as $idx) {
            $static = substr($route, $offset);

            $lastSegment = $routes[$idx][count($routes[$idx]) - 1];
            if (is_string($lastSegment) && str_ends_with($lastSegment, '/')) {
                $static = ltrim($static, '/');
            }

            if ($static !== '') {
                $routes[$idx][] = $static;
            }
        }

        // If all segments in the route are strings, join them all together.
        foreach (array_keys($routes) as $idx) {
            if (array_all($routes[$idx], fn(string|array $segment) => is_string($segment))) {
                // @phpstan-ignore argument.type
                $routes[$idx] = [join($routes[$idx])];
            }
        }

        return $routes;
    }
}
