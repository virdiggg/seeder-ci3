<?php

namespace Virdiggg\SeederCi3\Helpers;

class EnvHelper
{
    public function __construct()
    {
    }

    /**
     * Check if PHP version is below 7.
     * 
     * @return bool
     */
    public function belowPHP7() {
        $version = explode('.', PHP_VERSION);
        return $version[0] < 7;
    }

    /**
     * Verify if database.php file exists.
     * 
     * @param string $conn
     * 
     * @return string
     */
    public function verifyDBDir($conn) {
        // 'databse.php' should be inside config directory, or inside subdir ENVIRONMENT
        // ENVIRONMENT should have initialized when we installed CodeIgniter 3 otherwise the apps won't run,
        // so I don't think I have to verify whether ENVIRONMENT is set or not
        $defaultConfigFile = CI3_CONFIG_PATH . 'database.php';
        $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'database.php';
        if (file_exists($envConfigFile)) {
            include $envConfigFile;
        } elseif (file_exists($defaultConfigFile)) {
            include $defaultConfigFile;
        } else {
            // We're not able to find database.php file
            // So we return 'mysql' as it's default database driver when installing CodeIgniter 3
            return 'mysql';
        }

        // Access the dbdriver value dynamically
        if (isset($db[$conn]['dbdriver']) === false) {
            return 'mysql';
        }

        return $db[$conn]['dbdriver'];
    }

    /**
     * Load config file.
     * 
     * @param string $name
     * 
     * @return string|null
     */
    public function loadConfig($name = 'migration') {
        $defaultConfigFile = CI3_CONFIG_PATH . $name . '.php';
        $envConfigFile = CI3_CONFIG_PATH . ENVIRONMENT . DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($envConfigFile)) {
            include $envConfigFile;
            return $envConfigFile;
        } elseif (file_exists($defaultConfigFile)) {
            include $defaultConfigFile;
            return $defaultConfigFile;
        }
        return null;
    }

    /**
     * Get configuration from seeder.php and migration.php
     * 
     * @return object
     */
    public function getConfig() {
        include_once SEEDER_CONFIG_PATH;

        $this->loadConfig('migration');
        $this->loadConfig('seeder');

        $migrationType = $config['migration_type'];
        $migrationPath = $config['migration_path'];
        $dateTime = $config['date_time'];
        $dbConn = $config['db_conn'];
        $constructors = $config['constructors'];

        return (object) [
            'migrationType' => $migrationType,
            'migrationPath' => $migrationPath,
            'dateTime' => $dateTime,
            'dbConn' => $dbConn,
            'constructors' => $constructors,
        ];
    }
}