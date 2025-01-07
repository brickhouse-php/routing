<?php

namespace Brickhouse\Routing;

class RouteParser
{
    protected const string ROUTE_PATTERN = "/\/((?<quantifier>[\*:])(?<name>[\w-]+))/";

    /**
     * Parse the given route into an array of statics and arguments.
     *
     * @param string $route
     *
     * @return array
     */
    public function parse(string $route): array
    {
        if (!preg_match_all(self::ROUTE_PATTERN, $route, $matches, PREG_OFFSET_CAPTURE)) {
            return [$route];
        }

        $quantifierPatterns = [
            ':' => '[^/]+',
            '*' => '.*',
        ];

        $segments = [];
        $offset = 0;

        for ($i = 0; isset($matches[0][$i]); $i++) {
            [$match, $position] = $matches[1][$i];
            [$quantifier] = $matches['quantifier'][$i];
            [$name] = $matches['name'][$i];

            $segments[] = substr($route, $offset, $position - $offset);
            $segments[] = [$name => $quantifierPatterns[$quantifier]];

            $offset = $position + strlen($match);
        }

        return $segments;
    }
}
