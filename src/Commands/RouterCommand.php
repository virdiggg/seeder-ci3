<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\{File, Str};
use Virdiggg\SeederCi3\Parsers\{RouteParser, ControllerParser, InputParser, MethodParser, EndpointAnalyzer};
use Virdiggg\SeederCi3\Exporters\{PostmanExporter, OpenApiExporter, SwaggerUiExporter};

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

  /**
   * Controller method reader (docblocks, arguments, body).
   *
   * @var MethodParser
   */
  private $methodParser;

  /**
   * Endpoint analyzer (docs, params, rules, responses).
   *
   * @var EndpointAnalyzer
   */
  private $analyzer;

  const SPEC_FILE = 'swagger-api.json';

  private $routeParsed = [];

  private $storagePath = APPPATH . 'storage' . DIRECTORY_SEPARATOR;

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
    $this->methodParser = new MethodParser();
    $this->analyzer = new EndpointAnalyzer($this->inputParser);
  }

  public function handle()
  {
    try {
      $this->fl->ensureDirectoryExists($this->storagePath);

      $this->parser();
      $this->exportOpenApi();

      if ($this->input->option('postman')) {
        $this->export();
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

    $collectionFile = $this->exportCollection();
    $environmentFile = $this->exportEnv();

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

  private function exportCollection()
  {
    $collection = $this->exporter->export($this->routeParsed);

    $file = $this->storagePath . 'postman_collection.json';

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

  private function exportEnv()
  {
    $environment = $this->exporter->exportEnvironment($this->config->baseUrl);
    $file = $this->storagePath . 'postman_environment.json';

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

  /**
   * Path to the swagger.php view file, which is generated when --swagger is used.
   * 
   * @return string
   */
  private function viewPath()
  {
    $views = defined('VIEWPATH') ? VIEWPATH : APPPATH . 'views' . DIRECTORY_SEPARATOR;

    return rtrim($views, '/\\') . DIRECTORY_SEPARATOR . 'swagger.php';
  }

  /**
   * Generate swagger-api.json on every run, plus swagger.php with --swagger.
   *
   * Options: --swagger, --out=dir, --server=url, --title=, --api-version=,
   * --auth=bearer|basic|apikey
   */
  private function exportOpenApi()
  {
    $endpoints = $this->collectEndpoints();
    $unresolved = count(array_filter($endpoints, function ($endpoint) {
      return !$endpoint['resolved'];
    }));

    $title = $this->stringOption('title', 'Seeder CI3 API');
    $server = $this->resolveServer();
    $spec = (new OpenApiExporter())->export($endpoints, [
      'title' => $title,
      'version' => $this->stringOption('api-version', '1.0.0'),
      'server' => $server,
      'auth' => $this->stringOption('auth', null),
    ]);

    $dir = $this->outputDir();
    $this->fl->ensureDirectoryExists($dir);

    $files = [];
    $files['Swagger API File'] = $dir . self::SPEC_FILE;
    file_put_contents(
      $files['Swagger API File'],
      json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    $relDir = null;
    if ($this->input->option('swagger')) {
      $relDir = $this->relativeToWebRoot($dir);
      $files['Swagger UI File'] = $this->viewPath();
      file_put_contents(
        $files['Swagger UI File'],
        (new SwaggerUiExporter())->render($title, self::SPEC_FILE, $relDir)
      );
    }

    echo PHP_EOL;
    echo '======================================' . PHP_EOL;
    echo ' SWAGGER API GENERATED ' . PHP_EOL;
    echo '======================================' . PHP_EOL;
    echo PHP_EOL;

    foreach ($files as $label => $file) {
      echo str_pad($label, 17) . ': ' . $this->str->greenText($file);
    }

    echo str_pad('Server', 17) . ': ' . ($server
      ? $this->str->greenText($server, false)
      : "resolved in the browser from the swagger.php address\n");
    echo 'TOTAL ROUTES: ' . $this->str->greenText(count($endpoints), false);

    if ($unresolved > 0) {
      echo $this->str->yellowText($unresolved . ' route(s) had no controller method found; only route info was documented.');
    }

    if ($this->input->option('swagger') && $this->isPrivateDir($relDir)) {
      echo $this->str->yellowText('swagger.php is not in a public folder; use --out=docs (or copy both files to a public folder) to open it in a browser.');
    }

    echo PHP_EOL;
  }

  /**
   * Static "servers" entry for the spec, or null to let swagger.php decide
   * in the browser. CLI cannot know the published port or sub folder, so it
   * only uses what you state explicitly:
   *
   *   1. --server=https://api.example.com
   *   2. APP_URL / BASE_URL environment variable (e.g. compose "environment:")
   *   3. base_url from config.php, unless it is the bare http://localhost
   *      placeholder a CLI branch usually sets
   *
   * @return string|null
   */
  private function resolveServer()
  {
    $candidates = [
      $this->stringOption('server', null),
      getenv('APP_URL'),
      getenv('BASE_URL'),
      isset($this->config->baseUrl) ? $this->config->baseUrl : null,
    ];

    foreach ($candidates as $candidate) {
      if (!is_string($candidate) || trim($candidate) === '') {
        continue;
      }

      if (preg_match('#^https?://localhost/?$#i', trim($candidate))) {
        continue;
      }

      return rtrim(trim($candidate), '/');
    }

    return null;
  }

  /**
   * True when the directory is outside the web root or inside the
   * application folder, which a web server should not expose.
   *
   * @param string|null $relDir
   *
   * @return bool
   */
  private function isPrivateDir($relDir)
  {
    if ($relDir === null) {
      return true;
    }

    $appFolder = defined('APPPATH') ? basename(rtrim(APPPATH, '/\\')) : 'application';

    return strpos($relDir, $appFolder . '/') === 0;
  }

  /**
   * Directory of $dir relative to the web root (FCPATH), with trailing "/",
   * or null when it is not under the web root.
   *
   * @param string $dir
   *
   * @return string|null
   */
  private function relativeToWebRoot($dir)
  {
    if (!defined('FCPATH') || realpath(FCPATH) === false || realpath($dir) === false) {
      return null;
    }

    $root = rtrim(str_replace('\\', '/', realpath(FCPATH)), '/') . '/';
    $path = rtrim(str_replace('\\', '/', realpath($dir)), '/') . '/';

    if (strpos($path, $root) !== 0) {
      return null;
    }

    return (string) substr($path, strlen($root));
  }

  /**
   * Analyze every parsed route into a documented endpoint.
   *
   * @return array
   */
  private function collectEndpoints()
  {
    $endpoints = [];

    foreach ($this->routeParsed as $route) {
      $resolved = $this->methodParser->resolveTarget($route['target']);
      $method = $resolved ? $this->methodParser->parse($resolved['file'], $resolved['method']) : null;
      $extra = $resolved ? $resolved['extra'] : array_slice(explode('/', $route['target']), 3);

      // RouteParser assumes "api/Controller/method"; the resolved file is
      // authoritative for nested directories such as api/v1/Post/detail.
      if ($resolved) {
        $route['controller'] = $resolved['class'];
        $route['action'] = $resolved['method'];
      }

      $endpoints[] = $this->analyzer->analyze($route, $method, $extra);
    }

    return $endpoints;
  }

  /**
   * Output directory: --out=dir (relative to FCPATH) or application/storage.
   *
   * @return string with trailing separator
   */
  private function outputDir()
  {
    $out = $this->stringOption('out', null);

    if ($out === null) {
      return $this->storagePath;
    }

    $isAbsolute = $out[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $out);
    $base = defined('FCPATH') ? FCPATH : getcwd() . DIRECTORY_SEPARATOR;

    return rtrim($isAbsolute ? $out : $base . $out, '/\\') . DIRECTORY_SEPARATOR;
  }

  /**
   * Option value as string ("--title=My API"), ignoring bare flags.
   */
  private function stringOption($name, $default)
  {
    $value = $this->input->option($name);

    return is_string($value) && $value !== '' ? $value : $default;
  }
}
