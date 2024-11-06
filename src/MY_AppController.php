<?php

namespace Virdiggg\SeederCi3;
use Virdiggg\SeederCi3\Seeder;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class MY_AppController extends \CI_Controller
{
    public $seed;
    public $migrationType;
    public $dateTime = [];
    public $dbConn;
    public $migrationPath;
    private $str;

    /**
     * @param string $migrationType  Type of migration, sequential or timestamp. Default to 'sequential'.
     * @param array  $dateTime       List of additional table rows with datetime data type.
     *                               Default to "['created_at', 'updated_at', 'approved_at', 'deleted_at']".
     * @param string $dbConn         Name of database connection. Default to 'default'.
     * @param string $migrationPath  Path of migration file. Default to 'ROOT/application/migrations'.
     * */
    public function __construct(
        $migrationType = 'sequential',
        $dateTime = [],
        $dbConn = 'default',
        $migrationPath = APPPATH . 'migrations'
    )
    {
        parent::__construct();
        $this->str = new Str();
        $this->seed = new Seeder();
        // You can set which migration type you're using.
        $this->seed->setMigrationType($migrationType);
        // You can set which database connection you want to use.
        $this->seed->setConn($dbConn);
        // Migration path
        $this->seed->setPath($migrationPath);
        if (!empty($dateTime)) {
            $this->seed->addDateTime($dateTime);
        }
    }

    public function help() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        $this->seed->help();
        return;
    }

    public function migrate() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        $this->load->library('migration');

        if (!$this->migration->current()) {
            print($this->str->redText($this->migration->error_string()));
            return;
        }

        $res = $this->db->select('version')->from('migrations')->get()->row();
        print($this->str->greenText('MIGRATE NUMBER ' . $res->version . ' SUCCESS'));
        return;
    }

    public function rollback() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        $this->load->library('migration');

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $args = $result->args;

        $resOld = $this->db->select('version')->from('migrations')->get()->row();
        if (!isset($resOld->version)) {
            print($this->str->yellowText('No Migration Found'));
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
            print($this->str->redText($this->migration->error_string()));
            return;
        }

        $res = $this->db->select('version')->from('migrations')->get()->row();
        print($this->str->redText('ROLLBACK MIGRATION TO NUMBER ' . $res->version . ' SUCCESS'));
        return;
    }

    public function seed() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $this->seed->seed($name, $args);
    }

    public function migration() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $this->seed->migration($name, $args);
    }

    public function controller() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $this->seed->controller($name, $args);
        return;
    }

    public function model() {
        if (!is_cli()) {
            $this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n");
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $this->seed->model($name, $args);
        return;
    }
}