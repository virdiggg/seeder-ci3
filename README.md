# Library Seeder from Existing Database for CodeIgniter 3

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

## HOW TO USE
- Install this library with composer
```
composer require virdiggg/seeder-ci3
```
- Create a controller to host all the function from this library. Example is `application/App.php`
```
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

		print($msg);
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
```

### How to run migration: `php index.php <your controller name> <your function name>`.
```
cd c:/xampp/htdocs/codeigniter && php index.php app migrate
```
### How to create Seeder file: `php index.php <your controller name> <your function name> <table_name>`.
```
cd c:/xampp/htdocs/codeigniter && php index.php app seed users
```
### How to create Migration file: `php index.php <your controller name> <your function name> <table_name> [--args]`.
- Add `--soft-delete` to add soft delete parameter. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app migration users --soft-delete
```
### How to create Controller file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app controller Admin/Dashboard/Table --r
```
### How to create Model file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
- Add `--c` to generate its controller file as well. Optional.
- Add `--soft-delete` if your model using soft delete. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app model Admin/Users --r --c --soft-delete
```