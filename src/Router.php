<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Parsers\RouteParser;
use Virdiggg\SeederCi3\Parsers\ControllerParser;
use Virdiggg\SeederCi3\Parsers\InputParser;
use Virdiggg\SeederCi3\Exporters\PostmanExporter;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;
use Virdiggg\SeederCi3\Helpers\EnvHelper as Ev;

class Router {
    /**
     * Instance CI.
     *
     * @param object
     */
    private $CI;

    /**
     * Route parser.
     *
     * @var RouteParser
     */
    private $routeParser;

    /**
     * Controller parser.
     *
     * @var ControllerParser
     */
    private $controllerParser;

    /**
     * Input parser.
     *
     * @var InputParser
     */
    private $inputParser;

    /**
     * Exporter.
     *
     * @var PostmanExporter
     */
    private $exporter;

    private $routeParsed = [];
    private $str;
    private $env;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->str = new Str();
        $this->env = new Ev();
        $this->routeParser = new RouteParser();
        $this->controllerParser = new ControllerParser();
        $this->inputParser = new InputParser();
        $this->exporter = new PostmanExporter();
    }

    public function parse() {
        $routes = $this->routeParser->parse($this->env->loadConfig('routes'));
        $parsed = [];

        foreach ($routes as $route) {
            /*
            |--------------------------------------------------------------------------
            | Parse controller method body
            |--------------------------------------------------------------------------
            */
            $content = $this->controllerParser->parseMethodContent($route['target']);

            /*
            |--------------------------------------------------------------------------
            | Parse GET/POST params
            |--------------------------------------------------------------------------
            */
            $params = $this->inputParser->parse($content);

            $parsed[] = [
                'uri' => $route['uri'],
                'method' => $route['method'],
                'target' => $route['target'],
                'controller' => $route['controller'],
                'action' => $route['action'],
                'uri_params' => $route['uri_params'],
                'params' => $params,
            ];
        }

        $this->routeParsed = $parsed;
        return $parsed;
    }

    public function export() {
        if (count($this->routeParsed) === 0) {
            $this->parse();
        }

        $collection = $this->exporter->export($this->routeParsed);

        $storagePath = APPPATH . 'storage' . DIRECTORY_SEPARATOR;

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $file = $storagePath . 'postman_collection.json';

        file_put_contents($file,
            json_encode(
                $collection,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Console output
        |--------------------------------------------------------------------------
        */
        echo PHP_EOL;
        echo '======================================' . PHP_EOL;
        echo ' POSTMAN COLLECTION GENERATED ' . PHP_EOL;
        echo '======================================' . PHP_EOL;
        echo PHP_EOL;
        echo 'FILE: ' . $this->str->greenText($file);
        echo 'TOTAL ROUTES: ' . $this->str->greenText(count($this->routeParsed), false);
        echo PHP_EOL;
    }
}