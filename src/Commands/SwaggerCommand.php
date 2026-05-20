<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Parsers\RouteParser;
use Virdiggg\SeederCi3\Parsers\ControllerParser;
use Virdiggg\SeederCi3\Parsers\InputParser;
use Virdiggg\SeederCi3\Exporters\PostmanExporter;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class SwaggerCommand
{
    private $routeParser;
    private $controllerParser;
    private $inputParser;
    private $exporter;
    private $str;

    public function __construct()
    {
        $this->routeParser = new RouteParser();
        $this->controllerParser = new ControllerParser();
        $this->inputParser = new InputParser();
        $this->exporter = new PostmanExporter();
        $this->str = new Str();
    }

    /**
     * Generate Postman collection.
     *
     * @return void
     */
    public function handle()
    {
        $routes = $this->routeParser->parse(
            APPPATH . 'config' . DIRECTORY_SEPARATOR . 'routes.php'
        );

        $parsed = [];

        foreach ($routes as $route) {

            $content =
                $this->controllerParser
                    ->parseMethodContent(
                        $route['target']
                    );

            $params =
                $this->inputParser
                    ->parse($content);

            $parsed[] = [
                'uri' => $route['uri'],
                'method' => $route['method'],
                'target' => $route['target'],
                'params' => $params,
            ];
        }

        $collection =
            $this->exporter
                ->export($parsed);

        $path =
            APPPATH . 'storage' . DIRECTORY_SEPARATOR . 'postman_collection.json';

        /*
        |--------------------------------------------------------------------------
        | Create storage folder
        |--------------------------------------------------------------------------
        */
        if (!is_dir(APPPATH . 'storage')) {
            mkdir(APPPATH . 'storage', 0777, true);
        }

        file_put_contents(
            $path,
            json_encode(
                $collection,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            )
        );

        print($this->str->greenText('POSTMAN COLLECTION GENERATED: ' . $path . ' ヾ(•ω•`)o'));
    }
}