<?php

namespace Virdiggg\SeederCi3\Exporters;

class PostmanExporter
{
    /**
     * Export routes to Postman Collection.
     *
     * @param array  $routes
     * @param string $baseUrl
     *
     * @return array
     */
    public function export($routes, $baseUrl = '{{base_url}}')
    {
        $items = [];

        foreach ($routes as $route) {

            $request = [
                'method' => strtoupper($route['method']),
                'header' => [],
                'url' => [
                    'raw' => $baseUrl . '/' . $route['uri'],
                    'host' => [
                        $baseUrl
                    ],
                    'path' => explode('/', $route['uri']),
                ]
            ];

            /*
            |--------------------------------------------------------------------------
            | GET Params
            |--------------------------------------------------------------------------
            */
            if (!empty($route['params']['get'])) {

                $query = [];

                foreach ($route['params']['get'] as $param) {

                    $query[] = [
                        'key' => $param,
                        'value' => '',
                    ];
                }

                $request['url']['query'] = $query;
            }

            /*
            |--------------------------------------------------------------------------
            | POST Params
            |--------------------------------------------------------------------------
            */
            if (!empty($route['params']['post'])) {

                $body = [];

                foreach ($route['params']['post'] as $param) {

                    $body[] = [
                        'key' => $param,
                        'value' => '',
                        'type' => 'text',
                    ];
                }

                $request['body'] = [
                    'mode' => 'urlencoded',
                    'urlencoded' => $body,
                ];
            }

            $items[] = [
                'name' => $route['uri'],
                'request' => $request,
            ];
        }

        return [
            'info' => [
                'name' => 'Seeder CI3 API',
                '_postman_id' => md5(time()),
                'schema' =>
                'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'item' => $items,
        ];
    }

    /**
     * Export Postman Environment.
     *
     * @param string $baseUrl
     *
     * @return array
     */
    public function exportEnvironment($baseUrl)
    {
        return [
            'id' => md5($baseUrl),
            'name' => 'Seeder CI3 Environment',
            'values' => [
                [
                    'key' => 'base_url',
                    'value' => $baseUrl,
                    'enabled' => true,
                ]
            ],
            '_postman_variable_scope' => 'environment',
            '_postman_exported_at' => date('c'),
            '_postman_exported_using' => 'Seeder CI3',
        ];
    }
}
