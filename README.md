# Seeder CI3

A development utility library for CodeIgniter 3 inspired by Laravel Artisan and `orangehill/iseed`, designed to simplify migration, seeding, and boilerplate generation workflows in legacy CodeIgniter applications.

This package provides a lightweight CLI-style development toolkit for generating:

- Database seeders from existing tables
- Migration files
- Controllers
- Models
- Resource scaffolding

while maintaining compatibility with traditional CodeIgniter 3 project structures.

---

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

---

## Overview

CodeIgniter 3 lacks a native development workflow comparable to modern frameworks such as Laravel.

Seeder CI3 bridges that gap by introducing:

- Seeder generation from existing database data
- Migration management helpers
- Resource generators
- Automated boilerplate scaffolding
- CLI-style commands through CodeIgniter controllers

The library is particularly useful for:

- Legacy enterprise applications
- Internal business systems
- Teams maintaining long-lived CI3 projects
- Faster database replication for development/testing
- Rapid CRUD scaffolding

---

## Features

### Seeder Generation

Generate database seeders directly from existing table contents.

Useful for:

- Development environments
- QA datasets
- Staging replication
- Reference/master tables

---

### Migration Utilities

Includes migration helpers similar to Laravel Artisan workflows:

- Create migration files
- Run migrations
- Rollback migrations
- Organize migration directories

Supports both:

- Sequential migrations
- Timestamp migrations

---

### Resource Scaffolding

Generate:

- Controllers
- Models
- CRUD resources
- Soft delete support
- PostgreSQL-friendly helper methods

---

### PostgreSQL-Friendly Utilities

Includes helper methods such as:

- `storeOrUpdate()`

to simplify UPSERT-like workflows for PostgreSQL environments.

---

## Installation

Install via Composer:

```bash
composer require virdiggg/seeder-ci3 --dev
```

---

# Basic Setup

Create a controller to host Seeder CI3 commands.

Example:

`application/controllers/App.php`

```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;

class App extends MY_AppController
{
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

    public function rollback()
    {
        return;
    }

    public function __destruct()
    {
        if ($this->migrateCalled) {

            // Example post-migration hooks

            // $this->db->query("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO myrole");

            // $this->db->query("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO myrole");

        }
    }
}
```

---

# Publish Configuration

Generate the default configuration file:

```bash
php index.php app publish
```

This will generate:

```bash
application/config/seeder.php
```

---

# Configuration Example

```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['migration_type'] = 'timestamp';

$config['migration_path'] = APPPATH . 'migrations' . DIRECTORY_SEPARATOR;

$config['date_time'] = [];

$config['db_conn'] = 'default';

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

---

# Available Commands

## Show Help

```bash
php index.php app help
```

---

## Publish Configuration

```bash
php index.php app publish
```

---

## Organize Migration Files

```bash
php index.php app tidy
```

---

## Run Migrations

```bash
php index.php app migrate
```

---

## Rollback Migration

Rollback to a specific migration version:

```bash
php index.php app rollback --to=1
```

---

## Generate Seeder From Existing Table

Example:

```bash
php index.php app seed users --limit=10
```

Options:

- `--limit=10` → Limit generated rows

---

## Generate Migration File

Example:

```bash
php index.php app migration users --soft-delete
```

Options:

- `--soft-delete` → Add soft delete fields

---

## Generate Controller

Example:

```bash
php index.php app controller Admin/Dashboard/Table --r
```

Options:

- `--r` → Generate resource methods

---

## Generate Model

Example:

```bash
php index.php app model Admin/Users --r --c --m --soft-delete
```

Options:

- `--r` → Generate resource methods
- `--c` → Generate controller
- `--m` → Generate migration
- `--soft-delete` → Enable soft delete support

---

# PostgreSQL Helper: storeOrUpdate()

When using generated resource models with PostgreSQL, Seeder CI3 includes helper methods such as:

```php
storeOrUpdate()
```

This helper simplifies conditional insert/update workflows.

---

## Example With Explicit Conditions

```php
$param = [
    'name' => 'myname',
    'username' => 'myusername',
    'password' => password_hash('password1', PASSWORD_BCRYPT),
];

$conditions = [
    'name' => 'myname',
    'username' => 'myusername',
];

$res = $this->mymodel->storeOrUpdate($param, $conditions);
```

This will:

- Insert a row if no matching record exists
- Update the existing row otherwise

---

## Example Using Automatic Conditions

```php
$param = [
    'name' => 'myname',
    'username' => 'myusername',
    'password' => password_hash('password1', PASSWORD_BCRYPT),
];

$res = $this->mymodel->storeOrUpdate($param);
```

If no explicit conditions are provided:

- The library automatically derives conditions from `$param`
- Exception fields are excluded automatically

---

# Upgrade Guide: 1.x → 2.x

Version 2.x introduces lifecycle hooks for migration execution.

Your command controller should extend:

```php
MY_AppController
```

and optionally implement migration hooks through:

```php
__destruct()
```

Example:

```php
public function __destruct()
{
    if ($this->migrateCalled) {

        // Post-migration callbacks

    }
}
```

This enables:

- Automatic privilege grants
- Logging hooks
- Custom deployment logic
- Post-migration automation

---

# Recommended Use Cases

Seeder CI3 works especially well for:

- Legacy CodeIgniter 3 projects
- Enterprise internal systems
- PostgreSQL-backed CI3 applications
- Development environment replication
- Database snapshot seeding
- Rapid CRUD scaffolding
- Long-term maintenance projects

---

# Notes

This library focuses on improving developer experience for CodeIgniter 3 without fundamentally changing the framework architecture.

The goal is to provide modern development conveniences while remaining compatible with traditional CI3 application structures.
