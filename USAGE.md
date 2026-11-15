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
* Publishes templating engine (layout wrapper, example view, helper file)
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

---

## Swagger / OpenAPI

Every `router:list` run (with or without options) also writes `swagger-api.json` (OpenAPI 3.0.3)
next to the Postman files in `application/storage`.

```bash
php ci3 router:list                        # table + swagger-api.json
php ci3 router:list --swagger --out=docs   # + docs/swagger.php (Swagger UI)
php ci3 router:list --postman              # + Postman files
```

`swagger.php` is a small static page that loads `swagger-api.json` from its own directory,
so re-running `router:list` refreshes the docs without regenerating the page. It has to be
served over HTTP (browsers block `fetch()` from `file://`) and loads Swagger UI from a CDN.

| Option | Description |
| --- | --- |
| `--swagger` | Also write `swagger.php` |
| `--out=dir` | Output directory, relative to the project root or absolute. Default `application/storage` |
| `--server=URL` | Put this URL in the spec's `servers` |
| `--title="My API"` | `info.title` |
| `--api-version=1.2.0` | `info.version` (default `1.0.0`) |
| `--auth=bearer\|basic\|apikey` | Declare a global security scheme |

### Server URL

The CLI cannot know the port you published or the sub folder you deploy to, so the spec
only gets a `servers` entry when you state one, in this order:

1. `--server=https://api.example.com`
2. `APP_URL` or `BASE_URL` environment variable (e.g. `environment:` in compose.yml)
3. `base_url` from `config.php`, unless it is the bare `http://localhost` placeholder

Otherwise `servers` is left out and `swagger.php` works it out from the address you opened
it at (`http://localhost:8074/swagger` calls `http://localhost:8074`, and
`https://host/myapp/swagger` calls `https://host/myapp`), so the same files work in
a container, behind a proxy or in a sub folder. Add `?server=https://host/app` to the page URL
to override it for one session.

### What is detected automatically

* Path parameters: `users/(:num)` becomes `/users/{id}`. The name comes from the controller
  method argument that the route target maps with `$1`, `$2`, ...
* Query / body fields from `$this->input->get/post/input_stream/get_post()`, `$_GET`, `$_POST`
* Uploads from `$_FILES['x']` or `$this->upload->do_upload('x')` (`multipart/form-data`)
* JSON bodies from `php://input` / `raw_input_stream`
* Validation from `form_validation->set_rules()`: `required`, `integer`, `numeric`,
  `valid_email`, `valid_url`, `min_length`, `max_length`, `exact_length`, `greater_than`,
  `less_than`, `in_list`, `alpha*`, `is_natural*`. Fields that only appear in `set_rules()` are included.
* Status codes from `set_status_header()`, `http_response_code()`, `show_error()`, `show_404()`
* Routes without an HTTP verb that read request bodies are documented as `POST`

Commented-out code is ignored.

### Annotations

Add a docblock above the controller method:

```php
/**
 * Create a user.
 *
 * Longer description (optional).
 *
 * @tag Users
 * @query int page Page number
 * @body string email required Email address
 * @body file[] photos Profile photos
 * @param int $id The user id
 * @response 201 Created
 * @response 422 Validation failed
 * @auth bearer
 * @deprecated
 */
public function create() { ... }
```

| Tag | Meaning |
| --- | --- |
| first line | Summary |
| `@tag Name[, Name2]` | Group (default: controller name) |
| `@query type name [required] description` | Query parameter |
| `@body type name [required] description` | Request body field (`type`: `int`, `float`, `bool`, `string`, `file`, `type[]`) |
| `@param type $arg description` | Describe a path parameter (by method argument name) |
| `@response code description` | Response status |
| `@auth bearer\|basic\|apikey\|none` | Security for this endpoint (`none` removes the global one) |
| `@summary text` | Override summary |
| `@deprecated` | Mark deprecated |
| `@hidden` | Leave the endpoint out |

Annotations override anything inferred from the code.

Generated files are stored in:

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

## Laravel-like Template Example

```php
class Orders extends MY_Controller
{
    public function index()
    {
        return $this
            ->asTemplate('orders/edit', [
                'user' => 'asd123',
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

### asTemplate()

Render normal CI3 view with blade-like templating.

```php
$this->asTemplate('dashboard/index');
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
