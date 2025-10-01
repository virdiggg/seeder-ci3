<?php

namespace Virdiggg\SeederCi3\Helpers;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;
use Virdiggg\SeederCi3\Helpers\EnvHelper as Ev;

class FileHelper
{
    /**
     * File Pointer.
     *
     * @param object $filePointer
     */
    public $filePointer;
    private $str;
    private $env;

    public function __construct()
    {
        $this->str = new Str();
        $this->env = new Ev();
    }

    /**
     * Move all migration files inside 'migrated' folder.
     * If the folder does not exists, create a new one.
     * 
     * @param string $path
     * 
     * @return void
     */
    public function tidyingFiles($path) {
        try {
            $migratedDir = $path . 'migrated' . DIRECTORY_SEPARATOR;
            $this->folderPermission($migratedDir, 0755, 'apache');

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
     * Modify migration config file to update 'migration_version' value.
     *
     * @param string $currentMigration
     *
     * @return void
     */
    public function modifyConfig($currentMigration = '001') {
        $configFile = $this->env->loadConfig('migration');
        try {
            if (!$configFile) {
                throw new \Exception('Config file not found');
            }

            $contents = file_get_contents($configFile);
            if ($contents === false) {
                throw new \Exception("Unable to read {$configFile}");
            }

            $pieces = explode('$config', $contents);
            $version = array_filter($pieces, function($piece) {
                if ($this->str->startsWith($piece, "['migration_version'] = ")) {
                    return $piece;
                }
            });

            if (count($version) === 0) {
                throw new \Exception("'migration_version' not found in {$configFile}");
            }

            $version = $this->str->before(array_pop($version), ';');
            $fixedContents = str_replace($version, "['migration_version'] = {$currentMigration}", $contents);

            if (file_put_contents($configFile, $fixedContents) === false) {
                throw new \Exception("Unable to write back to {$configFile}");
            }
        } catch (\Throwable $th) {
            log_message('error', '[SeederCI3] Error loading migration config file: ' . $th->getMessage());
            return;
        }
        return;
    }

    /**
     * Create and write to file. If exists, do nothing.
     *
     * @param string $path
     * @param string $fileName
     * @param string $str
     *
     * @return bool
     */
    public function printFile($path, $fileName, $str)
    {
        $this->folderPermission($path, 0755, 'apache');

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
    private function folderPermission($path, $mode = 0755, $owner = 'www-data:www-data')
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

    /**
     * Copy seeder.php config file to config folder.
     *
     * @return void
     */
    public function copyConfig()
    {
        $defaultConfigFile = CI3_CONFIG_PATH . 'seeder.php';
        $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'seeder.php';
        if (file_exists($defaultConfigFile)) {
            $msg = $this->str->yellowText($defaultConfigFile) . $this->str->redText('already exists in your config folder. ╰(*°▽°*)╯') . "\n";
            print("\n".$msg);
            return;
        }

        if (file_exists($envConfigFile)) {
            $msg = $this->str->yellowText($envConfigFile) . $this->str->redText('already exists in your config folder. ╰(*°▽°*)╯') . "\n";
            print("\n".$msg);
            return;
        }

        // Copy the seeder.php file from package to config folder
        print($this->str->greenText("Copying 'seeder.php' to " . $defaultConfigFile, true));
        copy(SEEDER_CONFIG_PATH, $defaultConfigFile);
    }

    /**
     * Normalize path
     * 
     * @param string $path
     * 
     * @return string
     */
    public function normalizePath($path) {
        return rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
    }
}