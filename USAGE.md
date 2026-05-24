# Seeder CI3 Usage Guide

This document explains the available commands and development workflows provided by Seeder CI3.

---

# Command List

Show all available commands:

```bash
php ci3 help
```

---

# Check Current Version

Display the currently installed Seeder CI3 version.

```bash
php ci3 version
```

Useful for:

* Dependency auditing
* Upgrade verification
* CI/CD validation

---

# Initialize Seeder CI3

Initialize Seeder CI3 inside your CodeIgniter 3 project.

```bash
php ci3 init
```

This command automatically:

* Creates `App_ci3.php`
* Creates CLI bootstrap file `ci3`
* Publishes Seeder configuration
* Publishes migration hooks
* Migrates legacy migration hooks

---

# Organize Migration Files

Move executed migration files into the `migrated` directory.

```bash
php ci3 tidy
```

Useful for:

* Cleaning migration directories
* Organizing large projects
* Separating executed migrations

---

# Run Migrations

Execute all pending migrations.

```bash
php ci3 migrate
```

Seeder CI3 supports:

* Sequential migrations
* Timestamp migrations
* Multiple database connections

---

# Rollback Migration

Rollback the latest migration batch.

```bash
php ci3 rollback
```

---

## Rollback to Specific Version

```bash
php ci3 rollback --to=5
```

---

## Rollback Using Specific Database Connection

```bash
php ci3 rollback --db=pgsql
```

---

# Route List Generator

Print registered application routes directly in terminal.

```bash
php ci3 router:list
```

Useful for:

* API debugging
* Endpoint discovery
* Route auditing
* Internal documentation
* Legacy project maintenance

---

## Export Routes to Postman

```bash
php ci3 router:list --postman
```

Generates a Postman-compatible route collection and environment.

Currently optimized for:

```bash
application/controllers/api/*
```

Generated files are stored in

```bash
application/storage/
```

---

# Generate Model

Create a new model file.

```bash
php ci3 make:model Admin/Users
```

---

## Generate Resource Model

```bash
php ci3 make:model Admin/Users --r
```

Adds resource helper methods such as:

* insert
* update
* delete
* pagination helpers
* filtering helpers

---

## Generate Model + Controller

```bash
php ci3 make:model Admin/Users --c
```

Automatically generates its controller.

---

## Generate Model + Migration

```bash
php ci3 make:model Admin/Users --m
```

Automatically generates migration file.

---

## Generate Soft Delete Model

```bash
php ci3 make:model Admin/Users --soft-delete
```

Adds soft delete support automatically.

---

## Generate Faker-Enabled Model

```bash
php ci3 make:model Admin/Users --faker
```

Adds faker generation seeder for development datasets.

---

## Full Resource Example

```bash
php ci3 make:model Admin/Users --r --c --m --soft-delete --faker
```

---

# Generate Controller

Create a controller file.

```bash
php ci3 make:controller Admin/Dashboard
```

---

## Generate Resource Controller

```bash
php ci3 make:controller Admin/Dashboard --r
```

Adds common CRUD resource methods.

Useful for:

* REST APIs
* CRUD panels
* Admin dashboards

---

# Generate Migration

Create migration file.

```bash
php ci3 make:migration users
```

---

## Generate Migration With Soft Delete

```bash
php ci3 make:migration users --soft-delete
```

Automatically adds:

* `deleted_at`
* `deleted_by`

fields.

---

# Generate Seeder

Generate seeder from existing database table.

```bash
php ci3 make:seeder users
```

---

## Seeder With Row Limit

```bash
php ci3 make:seeder users --limit=10
```

Useful for:

* QA datasets
* Demo systems
* Development environments
* Lightweight seed snapshots

---

# Generate Faker Data

Generate faker-based development datasets.

```bash
php ci3 make:faker users
```

Seeder CI3 automatically analyzes table structure and attempts to generate contextual fake data.

---

## Faker With Row Limit

```bash
php ci3 make:faker users --limit=100
```

---

## Fallback Faker Fields

If table metadata cannot be resolved, Seeder CI3 falls back to default fields such as:

* `username`
* `full_name`

to ensure faker generation still works in minimal environments.

---

# Wrapper Controller

Seeder CI3 includes:

```php
Virdiggg\SeederCi3\MY_Controller
```

A lightweight wrapper controller for simplifying response workflows.

---

## JSON Response Example

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

## Available Helper Methods

### pretty()

Enable pretty JSON output.

```php
$this->pretty(true);
```

---

### asJson()

Return JSON response.

```php
$this->asJson([
    'status' => true
]);
```

---

### withData()

Share view data globally.

```php
$this->withData([
    'title' => 'Dashboard'
]);
```

---

### asView()

Render normal CI3 view.

```php
$this->asView('dashboard/index');
```

---

### asHtml()

Render CI3 view as HTML string.

```php
$html = $this->asHtml('emails/template');
```

Useful for:

* Email templates
* PDF rendering
* Background jobs
* Queue workers

---

# PostgreSQL Helper

Seeder CI3 includes:

```php
storeOrUpdate()
```

for PostgreSQL-friendly UPSERT-like workflows.

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

# Migration Hooks

Seeder CI3 supports:

* Pre-migration hooks
* Post-migration hooks
* Legacy hook migration

Useful for:

* Grant privilege automation
* Deployment pipelines
* Logging
* Database synchronization
* Environment preparation

---

# Recommended Workflow

Typical workflow:

```bash
php ci3 init

php ci3 make:model Admin/Users --r --c --m --faker

php ci3 migrate

php ci3 make:faker users --limit=100
```

This workflow quickly bootstraps:

* Migration
* Model
* Controller
* Faker dataset
* CRUD structure

for rapid development.
