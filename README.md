# A Simple Library Seeder from Existing Database for CodeIgniter 3

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

## Inspired from Laravel Artisan and [orangehill/iseed](https://github.com/orangehill/iseed) for Laravel.

### UPGRADE FROM 1.x to 2.x
- Modify your controller that host all the function from this library to something like this:
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
        parent::__construct();
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

    // The rest of your code

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
        parent::__construct();
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
- Create a config file named `seeder.php` or run `php index.php <your controller name> publish`
```bash
php index.php app publish
```
File should contains
```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Type of migration, sequential or timestamp. Default to 'timestamp'.
 * 
 * Optional, we will take the value from migration.php if not present
 */
$config['migration_type'] = 'timestamp';

/**
 * Path of migration file. Default to 'ROOT/application/migrations'.
 * 
 * Optional, we will take the value from migration.php if not present
 */
$config['migration_path'] = APPPATH . 'migrations' . DIRECTORY_SEPARATOR;

/**
 * List of additional table rows with datetime data type.
 * 
 * Default to "['created_at', 'updated_at', 'approved_at', 'deleted_at']".
 */
$config['date_time'] = [];

/**
 * Name of database connection. Default to 'default'.
 */
$config['db_conn'] = 'default';

/**
 * List of additional function to be called in constructor. Default to [].
 */
$config['constructors'] = [
    'controller' => [
        '$this->authenticated->isAuthenticated();',
    ],
    'model' => [
        '$this->load->helper("string");',
    ],
    'seed' => [
        '$this->load->helper("string");',
    ],
    'migration' => [
        '$this->load->helper("string");',
    ],
];
```

#### Help options: `php index.php <your controller name> help`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app help
```
#### Publish config file: `php index.php <your controller name> publish`.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app publish
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
When using [--r], you will have a function to create or update a row (storeOrUpdate), please read the comment before you decide to use them. Example:
```php
// In this code, we will insert a new user $param,
// only if there is no user with $conditions in the table
// So, insert into table when there is no row with name = 'myname'
// and username = 'myusername'
$param = [
    'name' => 'myname',
    'username' => 'myusername',
    'password' => password_hash('password1', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'created_by' => 'admin',
    'updated_by' => 'admin',
];
$conditions = [
    'name' => 'myname',
    'username' => 'myusername',
];
$res = $this->mymodel->storeOrUpdate($param, $conditions);
// In this code, we will insert a new user $param,
// but since we don't pass the second parameters,
// then we will use the first parameters as $conditions
// but only if they're not in list of $this->exceptions
// So, insert into table when there is no row with name = 'myname'
// and username = 'myusername' and password = hashed string, etc...
$param = [
    'name' => 'myname',
    'username' => 'myusername',
    'password' => password_hash('password1', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'created_by' => 'admin',
    'updated_by' => 'admin',
];
$res = $this->mymodel->storeOrUpdate($param);
```
- Add `--c` to generate its controller file as well. Optional.
- Add `--m` to generate its migration file as well. Optional.
- Add `--soft-delete` if your model using soft delete. Optional. When used along with `--m`, migration will have soft delete fields too.
```bash
cd c:/xampp/htdocs/codeigniter && php index.php app model Admin/Users --r --c --m --soft-delete
```
