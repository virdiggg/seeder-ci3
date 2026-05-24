<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Templates\SeederTemplate;
use Virdiggg\SeederCi3\Utils\{File, Str};

class MakeFakerCommand extends Command
{
  protected $input;
  protected $env;
  protected $fl;
  protected $str;
  protected $config;
  protected $prefix;

  public function __construct($input, $env)
  {
    $this->input = $input;
    $this->env = $env;
    $this->fl = new File();
    $this->str = new Str();
    $this->config = $env->getConfig();
    $this->prefix = 'faker';
  }

  public function handle()
  {
    try {
      $name = $this->input->argument(0);

      if (!$name) {
        throw new \Exception('Faker table name required');
      }

      $dbConn = $this->input->option('db') ?? $this->config->dbConn;

      $databases = $this->config->databases ?? [];
      if (!isset($databases[$dbConn])) {
        throw new \Exception('Database connection not found: ' . $dbConn);
      }

      $_params = $this->parsingParams($dbConn, $databases, $name);

      file_put_contents($_params['path'], $_params['content']);

      echo "Faker created: " . $this->str->greenText($_params['path']);

      $this->postEvents($_params['count']);
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to create faker: ' . $th->getMessage());
      return;
    }
  }

  private function parseName($dbConn, $name)
  {
    $tableName = strtolower(str_replace('\\', '_', $name));

    $db = get_instance()->load->database($dbConn, true);

    if ($db->table_exists($tableName)) {
      // Get all fields in this table
      $fields = $db->field_data($tableName);
    } else {
      echo $this->str->yellowText('Table "' . $tableName . '" not found in database, creating faker using dummy fields (❁´◡`❁)');

      $fields = [
        (object) [
          'name' => 'created_by',
          'type' => 'varchar',
          'primary_key' => 0,
        ],
        (object) [
          'name' => 'updated_by',
          'type' => 'varchar',
          'primary_key' => 0,
        ],
      ];
    }

    $rand = $this->str->rand(4);

    $migrationPath = $this->config->migrationPath;

    $count = $this->str->latest($this->config);

    return [
      'tableName' => $tableName,
      'migrationPath' => $migrationPath,
      'count' => $count,
      'class' => ucwords($this->prefix) . '_' . $tableName . '_' . $rand,
      'file' => $count . '_' . $this->prefix . '_' . $tableName . '_' . $rand,
      'fields' => $fields,
    ];
  }

  private function parsingParams($dbConn, $databases, $name)
  {
    $driver = $databases[$dbConn]['dbdriver'] ?? 'mysqli';

    $params = $this->input->options();
    if (!isset($params['limit'])) {
      $params['limit'] = (int) $this->config->limitSeed;
    } else {
      $params['limit'] = (int) $params['limit'];
    }

    $parsedName = $this->parseName($dbConn, $name);

    $constructors = $this->config->constructors['migration'] ?? [];

    $template = new SeederTemplate($dbConn, $driver, $this->config->dateTime);

    $content = $template->template(
      $parsedName['class'],
      $parsedName['tableName'],
      $parsedName['fields'],
      $params,
      $constructors
    );

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

  private function fetchResults($db, $tableName, $limit)
  {
    $db->select();
    $db->from(trim($tableName));
    if (!empty($limit)) {
      $db->limit($limit);
    }

    return $db->get()->result_array();
  }

  private function postEvents($count)
  {
    $this->fl->modifyConfig($count);
  }
}
