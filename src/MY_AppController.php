<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\{Router, Seeder};
use Virdiggg\SeederCi3\Utils\{Env, Str};

class MY_AppController extends \CI_Controller
{
    public $seed;
    public $router;
    private $str;
    private $env;
    private $constructors = [];

    public function __construct()
    {
        parent::__construct();
        $this->seed = new Seeder();
        $this->router = new Router();
        $this->str = new Str();
        $this->env = new Env();
        $config = $this->env->getConfig();

        $this->seed->setMigrationType($config->migrationType);
        $this->seed->setConn($config->dbConn);
        $this->seed->setPath($config->migrationPath);

        if (!is_cli()) {
            show_404();
            return;
        }

        // $config->dateTime should be an array
        if (count((array) $config->dateTime) > 0) {
            $this->seed->addDateTime($config->dateTime);
        }

        // $config->constructors should be an array
        if (count((array) $config->constructors) > 0) {
            $this->constructors = $config->constructors;
        }
    }

    public function index()
    {
        $kernel = new Console\Kernel($this->env);

        $kernel->handle($_SERVER['argv']);
    }

    public function init()
    {
        require_once __DIR__ . '/../bootstrap/install.php';
        return;
    }

    public function help() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function.");
        echo $this->str->yellowText("[WARNING] Please initialize the new CLI instance with 'php ci3 init'.");
    }

    public function migrate() {
        $this->load->library('migration');

        if (!$this->migration->current()) {
            print($this->str->redText($this->migration->error_string()));
            return;
        }

        echo $this->str->yellowText("[WARNING] You are using deprecated function to migrate.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 migrate' instead as this function will be benched in the next major release.");
        $res = $this->db->select('version')->from('migrations')->get()->row();
        print($this->str->greenText('MIGRATE NUMBER ' . $res->version . ' SUCCESS'));
        return;
    }

    public function rollback() {
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

        echo $this->str->yellowText("[WARNING] You are using deprecated function to rollback.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 rollback' instead as this function will be benched in the next major release.");
        $res = $this->db->select('version')->from('migrations')->get()->row();
        print($this->str->redText('ROLLBACK MIGRATION TO NUMBER ' . $res->version . ' SUCCESS'));
        return;
    }

    public function seed() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to create seeder file.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 make:seeder' instead as this function will be benched in the next major release.");
    }

    public function migration() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        $constructors = [];
        if (isset($this->constructors['migration'])) {
            $constructors = (array) $this->constructors['migration'];
        }
        echo $this->str->yellowText("[WARNING] You are using deprecated function to create migration file.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 make:migration' instead as this function will be benched in the next major release.");
        $this->seed->migration($name, $args, $constructors);
    }

    public function faker() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to create faker file.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 make:faker' instead as this function will be benched in the next major release.");
    }

    public function controller() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $constructors = [];
        if (isset($this->constructors['controller'])) {
            $constructors = (array) $this->constructors['controller'];
        }
        echo $this->str->yellowText("[WARNING] You are using deprecated function to create controller file.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 make:controller' instead as this function will be benched in the next major release.");
        $this->seed->controller($name, $args, $constructors);
        return;
    }

    public function model() {
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        $args = $result->args;

        // $this->seed->setPath(APPPATH);
        $constructors = [];
        if (isset($this->constructors['model'])) {
            $constructors = (array) $this->constructors['model'];
        }
        echo $this->str->yellowText("[WARNING] You are using deprecated function to create model file.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 make:model' instead as this function will be benched in the next major release.");
        $this->seed->model($name, $args, $constructors);
        return;
    }

    public function router() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to print routes.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 router:list' instead as this function will be benched in the next major release.");
    }

    public function publish() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to publish configuration files.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 init' instead as this function will be benched in the next major release.");
        $this->seed->copyConfig();
        return;
    }

    public function tidy() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to tidy migration files.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 tidy' instead as this function will be benched in the next major release.");
    }

    public function version() {
        echo $this->str->yellowText("[WARNING] You are using deprecated function to print version.");
        echo $this->str->yellowText("[WARNING] Please use initialize the new CLI instance with 'php ci3 init'.");
        echo $this->str->yellowText("[WARNING] Please use 'php ci3 version' instead as this function will be benched in the next major release.");
        print('Seeder CI3 Version ' . $this->str->greenText($this->env->getVersion(), false));
        return;
    }
}