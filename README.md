# A Simple Library Seeder from Existing Database for CodeIgniter 3

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

## Inspired from Laravel Artisan and [orangehill/iseed](https://github.com/orangehill/iseed) for Laravel.

### HOW TO USE
- Install this library with composer
```bash
composer require virdiggg/seeder-ci3 --dev
```
- Create a controller to host all the function from this library. Example is `application/controller/App.php`
```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;

class App extends MY_AppController
{
    public function __construct()
    {
        parent::__construct();
    }

    // If you don't wish to have rollback function
	public function rollback() {
		return;
	}
}
```

#### Help options: `php index.php <your controller name> help`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app help
```
#### How to run migration: `php index.php <your controller name> <your function name>`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app migrate
```
#### How to run rollback migration: `php index.php <your controller name> <your function name> [--args]`.
- Add `--to=1` to run migration number <args>. Optional. Default is the latest number in your database min 1.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app rollback --to=1
```
#### How to create Seeder file: `php index.php <your controller name> <your function name> <table_name>`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app seed users
```
#### How to create Migration file: `php index.php <your controller name> <your function name> <table_name> [--args]`.
- Add `--soft-delete` to add soft delete parameter. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app migration users --soft-delete
```
#### How to create Controller file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app controller Admin/Dashboard/Table --r
```
#### How to create Model file: `php index.php <your controller name> <your function name> <filename> [--args]`.
- Add `--r` to generate resources. Optional.
- Add `--c` to generate its controller file as well. Optional.
- Add `--soft-delete` if your model using soft delete. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app model Admin/Users --r --c --soft-delete
```
