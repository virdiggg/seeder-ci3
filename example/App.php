<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;

class App extends MY_AppController
{
    /**
     * Hooks for migrate() function.
     * If you want to run a callback after migrating a table,
     * ex.: create a log file after migration or run grant privileges query for a role.
     * 
     * @return bool $this
     */
    private $migrateCalled = false;
    public function __construct()
    {
        /**
         * You can pass argument here
         * @param string $migrationType  Type of migration, sequential or timestamp. Default to 'sequential'.
         * @param array  $dateTime       List of additional table rows with datetime data type.
         *                               Default to "['created_at', 'updated_at', 'approved_at', 'deleted_at']".
         * @param string $dbConn         Name of database connection. Default to 'default'.
         * @param string $migrationPath  Path of migration file. Default to 'ROOT/application/migrations'.
         * */
        parent::__construct('timestamp', ['create_date', 'change_date', 'last_access']);
    }

    public function migrate()
    {
        parent::migrate();
        $this->migrateCalled = true;
    }

    // If you don't wish to have rollback function
    public function rollback() {
        return;
    }

    public function __destruct()
    {
        if ($this->migrateCalled) {
            // $this->db->query("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO myrole");
            // $this->db->query("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO myrole");
        }
    }
}