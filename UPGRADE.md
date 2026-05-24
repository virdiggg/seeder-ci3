# Seeder CI3 Upgrade Guide

This document explains how to upgrade older Seeder CI3 installations to version 3.x and lists deprecated commands.

---

# Overview

Seeder CI3 v3 introduces:

* Dedicated CLI bootstrap (`php ci3`)
* Automatic initialization workflow
* Migration hook separation
* Improved command structure
* Wrapper controller utilities
* Cleaner migration lifecycle handling

Version 3.x is designed to modernize older Seeder CI3 projects while remaining compatible with existing CodeIgniter 3 applications.

---

# Upgrade Path

Supported upgrade paths:

* 1.x → 3.x
* 2.x → 3.x

---

# Upgrade: 2.x → 3.x

Upgrading from version 2.x is straightforward.

Run:

```bash
php index.php app init
```

or:

```bash
php ci3 init
```

This command will automatically:

* Create CLI bootstrap file
* Publish Seeder CI3 configuration
* Publish migration hooks
* Publish new entry point controller
* Migrate compatible legacy migration hooks

---

## Important Change

Migration lifecycle hooks should now be moved into:

```bash
application/hooks/PostMigration.php
```

or:

```bash
application/hooks/PreMigration.php
```

instead of placing them inside controller destructors.

---

## Recommended Migration Hook Structure

Example:

```php
class PostMigration
{
    public function handle()
    {
        $CI = &get_instance();

        // Custom migration hooks here
    }
}
```

Useful for:

* Grant privileges
* Deployment automation
* Logging
* Post-migration synchronization

---

# Upgrade: 1.x → 3.x

Version 1.x projects require a small manual setup before initialization.

---

## Step 1 — Create App_ci3.php

Create:

```bash
application/controllers/App_ci3.php
```

with the following content:

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

---

## Step 2 — Run Initialization

Run:

```bash
php index.php app init
```

or:

```bash
php ci3 init
```

Seeder CI3 will then:

* Publish configuration
* Create CLI bootstrap
* Publish migration hooks
* Upgrade compatible structures automatically

---

# Deprecated Commands

Starting from Seeder CI3 v3:

```bash
php index.php app <command>
```

is considered deprecated for most commands.

---

## Deprecated Command Mapping

| Deprecated Command             | Replacement               | Status                    |
| ------------------------------ | ------------------------- | ------------------------- |
| `php index.php app help`       | `php ci3 help`            | Not Working               |
| `php index.php app version`    | `php ci3 version`         | Still Working (Deprecated)|
| `php index.php app publish`    | `php ci3 init`            | Still Working (Deprecated)|
| `php index.php app migrate`    | `php ci3 migrate`         | Still Working (Deprecated)|
| `php index.php app rollback`   | `php ci3 rollback`        | Still Working (Deprecated)|
| `php index.php app seed`       | `php ci3 make:seeder`     | Not Working               |
| `php index.php app migration`  | `php ci3 make:migration`  | Still Working (Deprecated)|
| `php index.php app faker`      | `php ci3 make:faker`      | Not Working               |
| `php index.php app controller` | `php ci3 make:controller` | Still Working (Deprecated)|
| `php index.php app model`      | `php ci3 make:model`      | Still Working (Deprecated)|
| `php index.php app router`     | `php ci3 router:list`     | Not Working               |
| `php index.php app tidy`       | `php ci3 tidy`            | Not Working               |

---

# Recommended Command Style

Use:

```bash
php ci3 <command>
```

instead.

Example:

```bash
php ci3 migrate
php ci3 rollback
php ci3 make:model Users
```

---

# Exception

The following command remains valid and supported:

```bash
php index.php app init
```

This command is still supported to simplify legacy project upgrades.

---

# Deprecated: publish

The old:

```bash
php ci3 publish
```

command is deprecated.

Use:

```bash
php ci3 init
```

instead.

---

# Why The Changes?

Seeder CI3 v3 separates:

* Application runtime
* Migration lifecycle hooks
* CLI tooling
* Scaffolding commands

to provide:

* Cleaner project structure
* Easier maintenance
* Better upgrade paths
* More framework-native workflows

while preserving compatibility with legacy CodeIgniter 3 systems.

---

# Recommended Upgrade Workflow

For most projects:

```bash
composer update

php index.php app init

php ci3 version
```

Then move old migration hook logic into:

```bash
application/hooks/PostMigration.php
```

or:

```bash
application/hooks/PreMigration.php
```

depending on your use case.

---

# Notes

Seeder CI3 v3 focuses heavily on backward compatibility.

Most existing projects can upgrade with minimal changes, especially if migration-related logic is already centralized.

Projects with heavily customized migration destructors may require manual migration hook adjustments.
