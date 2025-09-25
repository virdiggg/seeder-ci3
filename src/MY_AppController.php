<?php

namespace Virdiggg\SeederCi3;
use Virdiggg\SeederCi3\Seeder;
use Virdiggg\SeederCi3\Config\MigrationConfig;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class MY_AppController extends \CI_Controller
{
    public $seed;
    private $str;
    private $constructors = [];

    /**
     * @param MigrationConfig|null $config
     * */
    public function __construct(MigrationConfig $config = null)
    {
        parent::__construct();
        $config = $config ?: new MigrationConfig();

        $this->str = new Str();
        $this->seed = new Seeder();
        $this->seed->setMigrationType($config->migrationType);
        $this->seed->setConn($config->dbConn);
        $this->seed->setPath($config->migrationPath);

        // $config->dateTime should be an array
        if (count((array) $config->dateTime) > 0) {
            $this->seed->addDateTime($config->dateTime);
        }

        // $config->constructors should be an array
        if (count((array) $config->constructors) > 0) {
            $this->constructors = $config->constructors;
        }
    }

    public function help() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        $this->seed->help();
        return;
    }

    public function migrate() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
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
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
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
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $constructors = [];
        if (isset($this->constructors['seed'])) {
            $constructors = (array) $this->constructors['seed'];
        }
        $this->seed->seed($name, $args, $constructors);
    }

    public function migration() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $constructors = [];
        if (isset($this->constructors['migration'])) {
            $constructors = (array) $this->constructors['migration'];
        }
        $this->seed->migration($name, $args, $constructors);
    }

    public function controller() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $constructors = [];
        if (isset($this->constructors['controller'])) {
            $constructors = (array) $this->constructors['controller'];
        }
        $this->seed->controller($name, $args, $constructors);
        return;
    }

    public function model() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $constructors = [];
        if (isset($this->constructors['model'])) {
            $constructors = (array) $this->constructors['model'];
        }
        $this->seed->model($name, $args, $constructors);
        return;
    }

    public function publish() {
        if (!is_cli()) {
            print($this->str->redText("CANNOT BE ACCESSED OUTSIDE COMMAND PROMP ╰(*°▽°*)╯\n"));
            return;
        }

        $this->seed->copyConfig();
        return;
    }
}