<?php

namespace Virdiggg\SeederCi3;
use Virdiggg\SeederCi3\Seeder;

class MY_AppController extends \CI_Controller
{
    public $seed;
    public $migrationType;
    public $dbConn;
    public $migrationPath;

    /**
     * @param string $migrationType  Type of migration, sequential or timestamp. Default to 'sequential'.
     * @param string $dbConn         Name of database connection. Default to 'default'.
     * @param string $migrationPath  Path of migration file. Default to 'ROOT/application/migrations'.
     * */
    public function __construct($migrationType = 'sequential', $dbConn = 'default', $migrationPath = APPPATH . 'migrations')
    {
        parent::__construct();
        $this->seed = new Seeder();
        // You can set which migration type you're using.
        $this->seed->setMigrationType($migrationType);
        // You can set which database connection you want to use.
        $this->seed->setConn($dbConn);
        // Migration path
        $this->seed->setPath($migrationPath);
    }

    public function help() {
        $msg = "
        php index.php app migrate                                             To run migration
        php index.php app rollback [--to=number]                              To rollback migration
        php index.php app seed [table_name]                                   To create seeder file based on table
        php index.php app migration [table_name] [--soft-delete]              To create migration file based on table
        php index.php app model [dir/model_name] [--r] [--c] [--soft-delete]  To create model file
        php index.php app controller [dir/controller_name] [--r]              To create controller file
        ";
        print("\033[92m" . $msg . "\033[0m \n");
        return;
    }

    public function migrate() {
        $this->load->library('migration');

        if (!$this->migration->current()) {
            show_error($this->migration->error_string());
            return;
        }

        $res = $this->db->select('version')->from('migrations')->get()->row();
        $msg = $this->seed->emoticon('MIGRATE NUMBER ' . $res->version . ' SUCCESS');

        print("\033[92m" . $msg . "\033[0m \n");
        return;
    }

    public function rollback() {
        $this->load->library('migration');

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $args = $result->args;

        $resOld = $this->db->select('version')->from('migrations')->get()->row();
        if (!isset($resOld->version)) {
            print('No Migration Found');
            return;
        }

        // Default to current number
        $version = $resOld->version === 1 ? 1 : $resOld->version - 1;

        foreach ($args as $arg) {
            if (strpos($arg, '--to=') !== false) {
                $version = substr($arg, strpos($arg, '--to=') + 5);
            }
        }

        if (!$this->migration->version((int) $version)) {
            show_error($this->migration->error_string());
            return;
        }

        $res = $this->db->select('version')->from('migrations')->get()->row();
        $msg = $this->seed->emoticon('ROLLBACK MIGRATION TO NUMBER ' . $res->version . ' SUCCESS');

        print("\033[92m" . $msg . "\033[0m \n");
        return;
    }

    public function seed() {
        // To add date time fields, the only date time fields we covers are 'created_at', 'updated_at', 'approved_at', 'deleted_at'
        $this->seed->addDateTime(['create_date', 'change_date', 'last_access']);
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        // $args = $result->args; // Seeder doesn't have arguments.

        $this->seed->seed($name);
    }

    public function migration() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $this->seed->migration($name, $args);
    }

    public function controller() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $this->seed->controller($name, $args);
        return;
    }

    public function model() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $this->seed->model($name, $args);
        return;
    }
}