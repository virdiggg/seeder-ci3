<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\Seeder;

class App extends CI_Controller
{
    public $seed;
    public function __construct()
    {
        parent::__construct();
        $this->seed = new Seeder();
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
		$result = $this->seeder->parseParam(func_get_args());
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
		// Get all arguments passed to this function
		$result = $this->seed->parseParam(func_get_args());
		$name = $result->name;
		// $args = $result->args; // Seeder doesn't have arguments.

        // You can set which database connection you want to use.
		// $this->seed->setConn('default2');
		// $this->seed->setPath(APPPATH);
		$this->seed->seed($name);
    }

    public function migration() {
		// Get all arguments passed to this function
		$result = $this->seed->parseParam(func_get_args());
		$name = $result->name;
		$args = $result->args;

        // You can set which database connection you want to use.
		// $this->seed->setConn('default2');
		// $this->seed->setPath(APPPATH);
		$this->seed->migration($name, $args);
    }

    public function controller() {
		// Get all arguments passed to this function

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

		// Get all arguments passed to this function
		$result = $this->seed->parseParam(func_get_args());
		$name = $result->name;
		$args = $result->args;

		// $this->seed->setPath(APPPATH);
		$this->seed->model($name, $args);
		return;
    }
}