<?php

namespace Virdiggg\SeederCi3\Parsers;

class RouteParser
{
    /**
     * Parse routes.php
     *
     * @param string $path
     *
     * @return array
     */
    public function parse($path)
    {
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        $routes = [];

        /*
        |--------------------------------------------------------------------------
        | Verb Routes
        |--------------------------------------------------------------------------
        |
        | $route['users']['GET'] = 'api/User/index';
        |
        */
        preg_match_all(
            '/\\$route\\[[\'"](.+?)[\'"]\\]\\[[\'"](.+?)[\'"]\\]\\s*=\\s*[\'"](.+?)[\'"]\\s*;/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $parsed = $this->buildRoute(
                $match[1],
                strtoupper($match[2]),
                $match[3]
            );

            if ($parsed) {
                $routes[] = $parsed;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Routes
        |--------------------------------------------------------------------------
        */
        preg_match_all(
            '/\\$route\\[[\'"](.+?)[\'"]\\]\\s*=\\s*[\'"](.+?)[\'"]\\s*;/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {

            /*
            |--------------------------------------------------------------------------
            | Skip already parsed verb routes
            |--------------------------------------------------------------------------
            */
            if (strpos($match[0], "']['") !== false) {
                continue;
            }

            $parsed = $this->buildRoute(
                $match[1],
                'GET',
                $match[2]
            );

            if ($parsed) {
                $routes[] = $parsed;
            }
        }

        return $routes;
    }

    /**
     * Build route info.
     *
     * @param string $uri
     * @param string $method
     * @param string $target
     *
     * @return array|null
     */
    private function buildRoute($uri, $method, $target)
    {
        /*
        |--------------------------------------------------------------------------
        | API Only
        |--------------------------------------------------------------------------
        */
        if (strpos($target, 'api/') !== 0) {
            return null;
        }

        $segments = explode('/', $target);

        /*
        |--------------------------------------------------------------------------
        | Minimum:
        | api/Controller/method
        |--------------------------------------------------------------------------
        */
        if (count($segments) < 3) {
            return null;
        }

        $controller = $segments[1];
        $action = $segments[2];

        /*
        |--------------------------------------------------------------------------
        | Parse URI Parameters
        |--------------------------------------------------------------------------
        */
        $params = $this->extractUriParams($uri);

        return [
            'uri' => $uri,
            'method' => strtoupper($method),
            'target' => $target,
            'controller' => $controller,
            'action' => $action,
            'uri_params' => $params,
        ];
    }

    /**
     * Extract URI params from route pattern.
     *
     * @param string $uri
     *
     * @return array
     */
    private function extractUriParams($uri)
    {
        $params = [];

        /*
        |--------------------------------------------------------------------------
        | (:num)
        |--------------------------------------------------------------------------
        */
        preg_match_all('/\\(:num\\)/', $uri, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $i => $v) {
                $params[] = [
                    'name' => 'num_' . ($i + 1),
                    'type' => 'number',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | (:any)
        |--------------------------------------------------------------------------
        */
        preg_match_all('/\\(:any\\)/', $uri, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $i => $v) {
                $params[] = [
                    'name' => 'any_' . ($i + 1),
                    'type' => 'string',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Regex Capture Groups
        |--------------------------------------------------------------------------
        |
        | ([a-z]+)
        | (\d+)
        | (.+)
        |
        */
        preg_match_all('/\\((?!\\:)(.*?)\\)/', $uri, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $i => $regex) {
                $type = 'string';

                if (
                    strpos($regex, '\\d') !== false ||
                    strpos($regex, '[0-9]') !== false
                ) {
                    $type = 'number';
                }

                $params[] = [
                    'name' => 'param_' . ($i + 1),
                    'type' => $type,
                    'regex' => $regex,
                ];
            }
        }

        return $params;
    }
}