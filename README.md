# Seeder CI3

Seeder CI3 is a modern CLI-style development toolkit for CodeIgniter 3 inspired by Laravel Artisan and `orangehill/iseed`.

It provides migration management, database seeding, CRUD scaffolding, faker generation, route inspection, and development helpers while remaining fully compatible with traditional CodeIgniter 3 applications.

Seeder CI3 is designed for developers maintaining legacy systems who still want modern development workflows without migrating away from CodeIgniter 3.

---

<img src="https://img.shields.io/packagist/php-v/virdiggg/seeder-ci3" /> <img src="https://img.shields.io/badge/codeigniter--version-3-green" /> <img src="https://img.shields.io/github/license/virdiggg/seeder-ci3" />

---

# Features

Seeder CI3 includes:

* Database seeder generation from existing tables
* Migration generation and execution
* Migration rollback support
* CRUD scaffolding
* Model and controller generators
* Built-in faker generation
* Route listing utilities
* Post-migration hooks
* PostgreSQL-friendly helpers
* Lightweight response wrapper controller
* CLI-style development workflow for CodeIgniter 3

---

# Installation

Install Seeder CI3 via Composer:

```bash
composer require virdiggg/seeder-ci3
```

---

# Quick Start

Create a controller new `application/controllers/App_ci3.php` for entry point:

```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;

class App_ci3 extends MY_AppController
{
    public function __construct()
    {
        parent::__construct();
    }
}
```

Initialize Seeder CI3 inside your project:

```bash
php index.php app_ci3 init
```

The initialization command will automatically:

* Create `App_ci3.php`
* Create CLI bootstrap file `ci3`
* Publish Seeder CI3 configuration
* Publish migration hooks
* Migrate legacy migration hooks automatically

---

# Generated Files

Initialization may generate:

```bash
application/controllers/App_ci3.php
application/config/seeder.php
application/hooks/PostMigration.php
application/hooks/PreMigration.php
ci3
```

---

# Basic Usage

Show available commands:

```bash
php ci3 help
```

Check installed version:

```bash
php ci3 version
```

Run migrations:

```bash
php ci3 migrate
```

Rollback migrations:

```bash
php ci3 rollback
```

Generate a model:

```bash
php ci3 make:model Admin/Users --r --c --m --soft-delete --faker
```

Generate a seeder:

```bash
php ci3 make:seeder users --soft-delete
```

Generate fake data:

```bash
php ci3 make:faker users
```

List application routes:

```bash
php ci3 router:list --postman
```

---

# Wrapper Controller

Seeder CI3 includes:

```php
Virdiggg\SeederCi3\MY_Controller
```

A lightweight wrapper controller designed to simplify:

* JSON responses
* Pretty JSON formatting
* Shared view data
* HTML rendering
* View rendering

Example:

```php
class Api extends MY_Controller
{
    public function users()
    {
        return $this
            ->pretty(true)
            ->asJson([
                'status' => true,
                'message' => 'Success'
            ]);
    }
}
```

---

# Migration Hooks

Seeder CI3 supports:

* Pre-migration hooks
* Post-migration hooks
* Legacy hook migration from old controller destructors

Useful for:

* Grant privilege automation
* Deployment workflows
* Logging
* Database synchronization
* Environment preparation

---

# Faker Support

Seeder CI3 can generate fake datasets directly from existing table structures.

Example:

```bash
php ci3 make:faker users
```

Supports:

* Table-based faker generation
* Default fallback fields
* Faker-enabled model scaffolding

---

# Route Utilities

Seeder CI3 can inspect routes directly from terminal:

```bash
php ci3 router:list
```

Supports:

* API route inspection
* Route auditing
* Endpoint discovery
* Postman export

Example:

```bash
php ci3 router:list --postman
```

Currently optimized for:

```bash
application/controllers/api/*
```

Generated files are stored in"

```bash
application/storage/
```

---

# Documentation

Additional documentation:

* [Usage Guide](./USAGE.md)
* [Upgrade Guide](./UPGRADE.md)

---

# Philosophy

Seeder CI3 aims to modernize the CodeIgniter 3 development experience without changing the framework's core architecture.

The goal is to provide practical developer tooling for long-term maintenance projects, enterprise systems, and legacy applications that still require fast and efficient workflows.
