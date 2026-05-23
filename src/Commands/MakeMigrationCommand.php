<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Templates\MigrationTemplate;
use Virdiggg\SeederCi3\Utils\{File, Str};

class MakeMigrationCommand extends Command
{
  protected $input;
  protected $env;
  protected $fl;
  protected $str;
  protected $config;

  public function __construct($input, $env)
  {
    $this->input = $input;
    $this->env = $env;
    $this->fl = new File();
    $this->str = new Str();
    $this->config = $env->getConfig();
  }

  public function handle()
  {
    try {
      $name = $this->input->argument(0);

      if (!$name) {
        throw new \Exception('Migration name required');
      }

      $dbConn = $this->input->option('db') ?? $this->config->dbConn;

      $databases = $this->config->databases ?? [];
      if (!isset($databases[$dbConn])) {
        throw new \Exception('Database connection not found: ' . $dbConn);
      }

      $_params = $this->parsingParams($dbConn, $databases, $name);

      file_put_contents($_params['path'], $_params['content']);

      echo "Migration created: " . $this->str->greenText($_params['path']);

      $this->postEvents($_params['count']);
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to create migration: ' . $th->getMessage());
      return;
    }
  }

  private function parseName($dbConn, $name, $params)
  {
    $tableName = strtolower(str_replace('\\', '_', $name));

    $db = get_instance()->load->database($dbConn, true);

    $prefix = 'create';
    if ($db->table_exists($tableName)) {
      $prefix = 'alter';
    }

    $rand = $this->str->rand(4);

    $migrationType = $this->config->migrationType ?? 'timestamp';

    $migrationPath = $this->config->migrationPath;

    $count = $this->str->latest($this->config);

    return [
      'tableName' => $tableName,
      'migrationPath' => $migrationPath,
      'count' => $count,
      'class' => ucwords($prefix) . '_' . $tableName . '_' . $rand,
      'file' => $count . '_' . $prefix . '_' . $tableName . '_' . $rand,
    ];
  }

  private function parsingParams($dbConn, $databases, $name)
  {
    $driver = $databases[$dbConn]['dbdriver'] ?? 'mysqli';

    $params = $this->input->options();

    $parsedName = $this->parseName($dbConn, $name, $params);

    $constructors = $this->config->constructors['migration'] ?? [];

    $template = new MigrationTemplate($driver);

    $content = $template->template($parsedName['class'], $parsedName['tableName'], $params, $constructors);

    $fileName = $parsedName['file'] . '.php';

    $path = rtrim($parsedName['migrationPath'], DIRECTORY_SEPARATOR);

    $this->fl->ensureDirectoryExists($path);

    return [
      'path' => $path . DIRECTORY_SEPARATOR . $fileName,
      'content' => $content,
      'name' => $name,
      'count' => $parsedName['count'],
    ];
  }

  private function postEvents($count)
  {
    $this->fl->modifyConfig($count);
  }
}
