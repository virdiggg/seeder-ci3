<?php

namespace Virdiggg\SeederCi3\Utils;

use Virdiggg\SeederCi3\Utils\Env;

class File
{
  /**
   * File Pointer.
   *
   * @param object $filePointer
   * 
   * @deprecated
   */
  public $filePointer;

  /**
   * Move all migration files inside 'migrated' folder.
   * If the folder does not exists, create a new one.
   * 
   * @param string $path
   * 
   * @return void
   */
  public function tidyingFiles($path)
  {
    try {
      $migratedDir = $path . 'migrated' . DIRECTORY_SEPARATOR;
      $this->ensureDirectoryExists($migratedDir, 0755, 'apache');

      $files = glob($path . '*.php');

      if (empty($files)) {
        throw new \Exception('No migration file found.');
      }

      foreach ($files as $file) {
        $fileName = basename($file);
        $destination = $migratedDir . $fileName;

        if (rename($file, $destination)) {
          log_message('info', "[SeederCI3] Migration file moved: $fileName -> migrated");
        } else {
          throw new \Exception("Failed to move: $fileName");
        }
      }
    } catch (\Throwable $th) {
      log_message('error', '[SeederCI3] Error tidying migration files: ' . $th->getMessage());
      return;
    }
  }

  /**
   * Create folder with 0755 (rwxr-xr-x) permission if doesn't exist.
   * If exists, change its permission to 0755 (rwxrwxrwx).
   * Owner default to www-data:www-data.
   *
   * @param string $path
   * @param string $mode
   * @param string $owner
   *
   * @return void
   */
  public function ensureDirectoryExists($path, $mode = 0755, $owner = 'www-data:www-data')
  {
    if (!is_dir($path)) {
      // If folder doesn't exist, create a new one with permission (rwxrwxrwx).
      $old = umask(0);
      mkdir($path, $mode, TRUE);
      @chown($path, $owner);
      // @chgrp($path, $owner);
      umask($old);
    } else {
      // If exists, change its permission to 0755 (rwxr-xr-x).
      $old = umask(0);
      @chmod($path, $mode);
      @chown($path, $owner);
      // @chgrp($path, $owner);
      umask($old);
    }
  }

  public function modifyConfig($currentMigration = '001')
  {
    $env = new Env();
    $migrationConfig = $env->loadConfig('migration');
    $configFile = $migrationConfig['file_path'];

    try {
      if (!$configFile) {
        throw new \Exception('Migration config file not found');
      }

      $contents = file_get_contents($configFile);

      if ($contents === false) {
        throw new \Exception("Unable to read " . $configFile);
      }

      $pattern = "/\\\$config\\['migration_version'\\]\\s*=\\s*['\"]?([0-9]+)['\"]?\\s*;/";

      if (!preg_match($pattern, $contents)) {
        throw new \Exception("'migration_version' not found in " . $configFile);
      }

      $replacement = "\$config['migration_version'] = '" . $currentMigration . "';";

      $fixedContents = preg_replace($pattern, $replacement, $contents, 1);

      if ($fixedContents === null) {
        throw new \Exception('Regex replacement failed');
      }

      if (file_put_contents($configFile, $fixedContents) === false) {
        throw new \Exception("Unable to write " . $configFile);
      }
    } catch (\Throwable $th) {
      log_message('error', '[SeederCI3] ' . $th->getMessage());

      return false;
    }

    return true;
  }

  /**
   * Copy seeder.php config file to config folder.
   *
   * @return void
   * 
   * @deprecated
   */
  public function copyConfig()
  {
    $defaultConfigFile = CI3_CONFIG_PATH . 'seeder.php';
    $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'seeder.php';
    if (file_exists($defaultConfigFile)) {
      $msg = $this->str->yellowText($defaultConfigFile) . $this->str->redText('already exists in your config folder. ╰(*°▽°*)╯') . "\n";
      print("\n" . $msg);
      return;
    }

    if (file_exists($envConfigFile)) {
      $msg = $this->str->yellowText($envConfigFile) . $this->str->redText('already exists in your config folder. ╰(*°▽°*)╯') . "\n";
      print("\n" . $msg);
      return;
    }

    // Copy the seeder.php file from package to config folder
    print($this->str->greenText("Copying 'seeder.php' to " . $defaultConfigFile, true));
    copy(SEEDER_CONFIG_PATH, $defaultConfigFile);
  }

  /**
   * Create and write to file. If exists, do nothing.
   *
   * @param string $path
   * @param string $fileName
   * @param string $str
   *
   * @return bool
   * 
   * @deprecated
   */
  public function printFile($path, $fileName, $str)
  {
    $this->ensureDirectoryExists($path, 0755, 'apache');

    $fullPath = $path . $fileName;
    // If file exists, stop the process.
    if (file_exists($fullPath)) {
      print("FILE EXISTS: " . $this->str->yellowText($fullPath));
      return false;
    }

    $this->createFile($path, $fileName);
    // Write to newly created migration file.
    fwrite($this->filePointer, $str . PHP_EOL);
    return true;
  }

  /**
   * Create a new pointer file.
   *
   * @param string $path
   * @param string $name
   *
   * @return void
   * 
   * @deprecated
   */
  private function createFile($path, $name)
  {
    $fullPath = $path . $name;

    $old = umask(0);

    $file = $fullPath;
    $file = fopen($fullPath, 'a') or exit("Can't open $fullPath!");
    umask($old);

    $this->filePointer = $file;
  }

  /**
   * Normalize path
   * 
   * @param string $path
   * 
   * @return string
   * 
   * @deprecated
   */
  public function normalizePath($path)
  {
    return rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
  }
}
