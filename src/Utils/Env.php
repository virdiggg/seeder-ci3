<?php

namespace Virdiggg\SeederCi3\Utils;

class Env
{
  /**
   * Check if PHP version is below 7.
   * 
   * @return bool
   */
  public function belowPHP7()
  {
    $version = explode('.', PHP_VERSION);
    return $version[0] < 7;
  }

  /**
   * Load config file.
   * 
   * @param string $name
   * 
   * @return array
   */
  public function loadConfig($name = 'migration')
  {
    $defaultConfigFile = CI3_CONFIG_PATH . $name . '.php';
    $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . $name . '.php';
    $file = null;

    if (file_exists($envConfigFile)) {
      $file = $envConfigFile;
    } elseif (file_exists($defaultConfigFile)) {
      $file = $defaultConfigFile;
    }

    if (!$file) {
      return [];
    }

    $config = [];

    include $file;

    return array_merge($config, ['file_path' => $file]);
  }

  /**
   * Get configuration from config.php, database.php, seeder.php, and migration.php
   * 
   * @return object
   */
  public function getConfig()
  {
    $migrationConfig = $this->loadConfig('migration');
    $seederConfig = $this->loadConfig('seeder');
    $configConfig = $this->loadConfig('config');
    $db = $this->loadDatabaseConfig();

    $config = array_merge(
      $migrationConfig,
      $seederConfig,
      $configConfig
    );

    return (object) [
      'migrationType' => $config['migration_type'] ?? 'timestamp',
      'migrationPath' => $config['migration_path'] ?? APPPATH . 'migrations/',
      'migrationVersion' => $config['migration_version'] ?? '001',
      'dateTime' => $config['date_time'] ?? [],
      'dbConn' => $config['db_conn'] ?? 'default',
      'constructors' => $config['constructors'] ?? [],
      'limitSeed' => $config['limit_seed'] ?? 10,
      'allowRollback' => $config['allow_rollback'] ?? false,
      'baseUrl' => $config['base_url'] ? rtrim($config['base_url'], '/') : 'http://localhost',
      'databases' => $db,
    ];
  }

  /**
   * Load database configuration from database.php file.
   * 
   * @return array
   */
  public function loadDatabaseConfig()
  {
    $defaultConfigFile = CI3_CONFIG_PATH . 'database.php';

    $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'database.php';

    $file = file_exists($envConfigFile) ? $envConfigFile : $defaultConfigFile;

    if (!file_exists($file)) {
      return [];
    }

    $db = [];

    include $file;

    return $db;
  }
}
