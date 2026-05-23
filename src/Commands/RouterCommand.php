<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\{File, Str};
use Virdiggg\SeederCi3\Parsers\{RouteParser, ControllerParser, InputParser};
use Virdiggg\SeederCi3\Exporters\PostmanExporter;

class RouterCommand extends Command
{
  protected $input;
  protected $env;
  protected $fl;
  protected $str;
  protected $config;

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

  public function __construct($input, $env, $e)
  {
    $this->input = $input;
    $this->env = $env;
    $this->fl = new File();
    $this->str = new Str();
    $this->config = $env->getConfig();

    $this->routeParser = new RouteParser();
    $this->controllerParser = new ControllerParser();
    $this->inputParser = new InputParser();
    $this->exporter = new PostmanExporter();
  }

  public function handle()
  {
    try {
      if ($this->input->option('postman')) {
        $this->export();
      } else {
        $this->parser();
      }
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to retrieve route list: ' . $th->getMessage());
      return;
    }
  }

  private function parser()
  {
    $routes = $this->routeParser->parse($this->env->loadConfig('routes')['file_path']);
    $parsed = [];

    foreach ($routes as $route) {
      // Parse controller method body
      $content = $this->controllerParser->parseMethodContent($route['target']);
      // Parse GET/POST params
      $params = $this->inputParser->parse($content);
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
    $this->str->renderTable($this->routeParsed);
  }

  private function export()
  {
    if (count($this->routeParsed) === 0) {
      $this->parser();
    }

    $storagePath = APPPATH . 'storage' . DIRECTORY_SEPARATOR;

    $this->fl->ensureDirectoryExists($storagePath);

    $collectionFile = $this->exportCollection($storagePath);
    $environmentFile = $this->exportEnv($storagePath);

    // Console output
    echo PHP_EOL;
    echo '======================================' . PHP_EOL;
    echo ' POSTMAN COLLECTION GENERATED ' . PHP_EOL;
    echo '======================================' . PHP_EOL;
    echo PHP_EOL;
    echo 'Postman Collection File : ' . $this->str->greenText($collectionFile);
    echo 'Environment File        : ' . $this->str->greenText($environmentFile);
    echo 'TOTAL ROUTES: ' . $this->str->greenText(count($this->routeParsed), false);
    echo PHP_EOL;
  }

  private function exportCollection($storagePath)
  {
    $collection = $this->exporter->export($this->routeParsed);

    $file = $storagePath . 'postman_collection.json';

    file_put_contents(
      $file,
      json_encode(
        $collection,
        JSON_PRETTY_PRINT |
          JSON_UNESCAPED_UNICODE |
          JSON_UNESCAPED_SLASHES
      )
    );

    return $file;
  }

  private function exportEnv($storagePath)
  {
    $environment = $this->exporter->exportEnvironment($this->config->baseUrl);
    $file = $storagePath . 'postman_environment.json';

    file_put_contents(
      $file,
      json_encode(
        $environment,
        JSON_PRETTY_PRINT |
          JSON_UNESCAPED_UNICODE |
          JSON_UNESCAPED_SLASHES
      )
    );

    return $file;
  }
}
