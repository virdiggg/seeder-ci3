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
    }

    public function printFile($path, $fileName, $str)
    {
        $this->createFile($path, $fileName);
        // Write to newly created migration file.
        fwrite($this->filePointer, $str . PHP_EOL);
    }

    /**
     * Create seeder file. Drop if already exists, then create a new one.
     *
     * @param string $path
     * @param string $name
     *
     * @return void
     */
    private function createFile($path, $name)
    {
        $this->folderPermission($path, 0755, 'apache');

        $fullPath = $path . $name;

        $old = umask(0);

        $file = $fullPath;
        // If file exists, drop it.
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
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
}