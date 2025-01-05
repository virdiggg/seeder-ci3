<?php

namespace Virdiggg\SeederCi3\Config;

class MigrationConfig
{
    /*
     * @param string $migrationType  Type of migration, sequential or timestamp. Default to 'sequential'.
     * @param array  $dateTime       List of additional table rows with datetime data type.
     *                               Default to "['created_at', 'updated_at', 'approved_at', 'deleted_at']".
     * @param string $dbConn         Name of database connection. Default to 'default'.
     * @param string $migrationPath  Path of migration file. Default to 'ROOT/application/migrations'.
     * @param array  $constructors   List of additional constructor parameters. Default to [].
     * */
    public $migrationType = 'sequential';
    public $dateTime = [];
    public $dbConn = 'default';
    public $migrationPath = APPPATH . 'migrations';
    public $constructors = [];
}
