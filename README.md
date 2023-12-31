# A Simple Library Seeder from Existing Database for CodeIgniter 3

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

## Inspired from Laravel Artisan and [orangehill/iseed](https://github.com/orangehill/iseed) for Laravel.

### HOW TO USE
- Install this library with composer
```
composer require virdiggg/seeder-ci3 --dev
```
- Optional, update your `composer.json` and add this line
```
"scripts": {
    "post-install-cmd": [
        "@php -r \"copy('vendor/virdiggg/seeder-ci3/example/App.php', 'controllers/App.php');\""
    ]
}
```
- Create a controller to host all the function from this library. Example is `application/controller/App.php`
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
        // $this->seed->addDateTime(['create_date', 'change_date', 'last_access']);
        // Get all arguments passed to this function
        $result = $this->seed->parseParam(func_get_args());
        $name = $result->name;
        // $args = $result->args; // Seeder doesn't have arguments.

        // You can set which database connection you want to use.
        // $this->seed->setConn('default2');
        // $this->seed->setPath(APPPATH);
        // You can set which migration type you're using.
        $this->seed->setMigrationType('sequential');
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
        // You can set which migration type you're using.
        $this->seed->setMigrationType('timestamp');
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
```

#### How to run migration: `php index.php <your controller name> <your function name>`.
```
cd c:/xampp/htdocs/codeigniter && php index.php app migrate
```
#### How to run rollback migration: `php index.php <your controller name> <your function name> [--args]`.
- Add `--to=1` to run migration number <args>. Optional. Default is the latest number in your database min 1.
```
cd c:/xampp/htdocs/codeigniter && php index.php app rollback --to=1
```
#### How to create Seeder file: `php index.php <your controller name> <your function name> <table_name>`.
```
cd c:/xampp/htdocs/codeigniter && php index.php app seed users
```
#### How to create Migration file: `php index.php <your controller name> <your function name> <table_name> [--args]`.
- Add `--soft-delete` to add soft delete parameter. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app migration users --soft-delete
```
#### How to create Controller file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app controller Admin/Dashboard/Table --r
```
#### How to create Model file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
- Add `--c` to generate its controller file as well. Optional.
- Add `--soft-delete` if your model using soft delete. Optional.
```
cd c:/xampp/htdocs/codeigniter && php index.php app model Admin/Users --r --c --soft-delete
```
