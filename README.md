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
            // log_message('error', 'PREVILEGES GRANTED');
        }
    }
}
```

#### Help options: `php index.php <your controller name> help`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app help
```
#### How to run migration: `php index.php <your controller name> migrate`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app migrate
```
#### How to run rollback migration: `php index.php <your controller name> rollback [--args]`.
- Add `--to=1` to run migration number <args>. Optional. Default is the latest number in your database min 1.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app rollback --to=1
```
#### How to create Seeder file: `php index.php <your controller name> <your function name> seed [--args]`.
- Add `--limit=5` to limit the query result. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app seed users --limit=10
```
#### How to create Migration file: `php index.php <your controller name> <your function name> migration [--args]`.
- Add `--soft-delete` to add soft delete parameter. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app migration users --soft-delete
```
#### How to create Controller file: `php index.php <your controller name> controller <filename> [--args]`.
- Add `--r` to generate resources. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app controller Admin/Dashboard/Table --r
```
#### How to create Model file: `php index.php <your controller name> model <filename> [--args]`.
- Add `--r` to generate resources. Optional.
- Add `--c` to generate its controller file as well. Optional.
- Add `--m` to generate its migration file as well. Optional.
- Add `--soft-delete` if your model using soft delete. Optional.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app model Admin/Users --r --c --m --soft-delete
```
