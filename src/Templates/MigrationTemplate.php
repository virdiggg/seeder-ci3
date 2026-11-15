<?php

namespace Virdiggg\SeederCi3\Templates;

use Virdiggg\SeederCi3\Utils\Str;

class MigrationTemplate
{
  private $driver;
  private $str;

  public function __construct($driver = 'mysqli')
  {
    $this->driver = $driver;
    $this->str = new Str();
  }

  /**
   * Parse input as printable string for migration file.
   *
   * @param string $name
   * @param string $tableName
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function template($name, $tableName, $param, $constructors = [])
  {
    return $this->str->startsWith($name, 'Alter_')
      ? $this->alterTable($name, $tableName, $param, $constructors)
      : $this->createTable($name, $tableName, $param, $constructors);
  }

  /**
   * Prepare create table template
   * 
   * @param string $name
   * @param string $tableName
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function createTable($name, $tableName, $param, $constructors = [])
  {
    $softDelete = $this->softDelete($param);

    $constructorCode = '';

    foreach ($constructors as $constructor) {
      $constructorCode .= '        ' . $constructor . PHP_EOL;
    }

    $postgresIndex = '';

    if (in_array($this->driver, ['postgre', 'pgasync'])) {
      $postgresIndex = <<<'PHP'
      // Uncomment if you want to create index for this table.
      // Recommended if this table doesn't have UPDATE and DELETE operations. PostgreSQL only.
      // $this->db->query('CREATE INDEX CONCURRENTLY ON "'.$this->name.'" ("'.join('", "', array_keys($this->fields)).'")');
PHP;
    }

    return str_replace(
      [
        '@@NAME@@',
        '@@TABLE_NAME@@',
        '@@SOFT_DELETE@@',
        '@@CONSTRUCTORS@@',
        '@@POSTGRES_INDEX@@',
      ],
      [
        $name,
        $tableName,
        $softDelete,
        $constructorCode,
        $postgresIndex,
      ],
      self::CREATE_TEMPLATE
    );
  }

  /**
   * Prepare alter table template
   *
   * @param string $name
   * @param string $tableName
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function alterTable($name, $tableName, $param, $constructors = [])
  {
    $softDelete = $this->softDelete($param);

    $constructorCode = '';

    foreach ($constructors as $constructor) {
      $constructorCode .= '        ' . $constructor . PHP_EOL;
    }

    return str_replace(
      [
        '@@NAME@@',
        '@@TABLE_NAME@@',
        '@@SOFT_DELETE@@',
        '@@SOFT_DELETE_OLD@@',
        '@@CONSTRUCTORS@@',
      ],
      [
        $name,
        $tableName,
        $softDelete,
        $softDelete,
        $constructorCode,
      ],
      self::ALTER_TEMPLATE
    );
  }

  /**
   * Param soft delete
   * 
   * @param array $param
   * 
   * @return string
   */
  private function softDelete($param)
  {
    if (empty($param) || !isset($param['soft-delete'])) {
      return '';
    }

    return <<<'PHP'
            'deleted_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
PHP;
  }

  const CREATE_TEMPLATE = <<<'PHP'
<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_Migration;

class Migration_@@NAME@@ extends MY_Migration
{
    public function __construct()
    {
        parent::__construct();

        /**
         * Table name.
         *
         * @var string
         */
        $this->name = "@@TABLE_NAME@@";

        /**
         * Primary key.
         *
         * @var string
         */
        $this->primary = "id";

        /**
         * Array table fields.
         *
         * @var array
         */
        $this->fields = [
            $this->primary => [
                'type' => 'BIGINT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
@@SOFT_DELETE@@
        ];

@@CONSTRUCTORS@@
    }

    /**
     * Migration.
     *
     * @return void
     */
    public function up()
    {
        parent::up();
@@POSTGRES_INDEX@@
    }

    /**
     * Rollback migration.
     *
     * @return void
     */
    public function down()
    {
        parent::down();
    }
}
PHP;

  const ALTER_TEMPLATE = <<<'PHP'
<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_Alter;

class Migration_@@NAME@@ extends MY_Alter
{
    public function __construct()
    {
        parent::__construct();

        /**
         * Table name.
         *
         * @var string
         */
        $this->name = "@@TABLE_NAME@@";

        /**
         * Array table fields.
         *
         * @var array
         */
        $this->fields = [
            // 'old_name' => [
            //     'name' => 'new_name', // if you want to modify its name
            //     'type' => 'VARCHAR',
            //     'constraint' => 150,
            //     'null' => TRUE,
            // ],
            // 'old_name' => [
            //     'name' => 'new_name',
            //     'type' => 'VARCHAR',
            //     'constraint' => 150,
            //     'null' => TRUE,
            // ],
@@SOFT_DELETE@@
        ];

        /**
         * Array table old fields. For the purpose of rolling back a migration.
         *
         * @var array
         */
        $this->oldFields = [
@@SOFT_DELETE_OLD@@
            // 'new_name' => [
            //     'name' => 'old_name',
            //     'type' => 'VARCHAR',
            //     'constraint' => 150,
            //     'null' => TRUE,
            // ],
            // 'new_name' => [
            //     'name' => 'old_name',
            //     'type' => 'VARCHAR',
            //     'constraint' => 150,
            //     'null' => TRUE,
            // ],
        ];

@@CONSTRUCTORS@@
    }

    /**
     * Migration.
     *
     * @return void
     */
    public function up()
    {
        parent::up();

        $this->dbforge->add_column($this->name, $this->fields);
        // $this->dbforge->modify_column($this->name, $this->fields);
    }

    /**
     * Rollback migration.
     *
     * @return void
     */
    public function down()
    {
        parent::down();

        foreach ($this->fields as $index => $name) {
            $this->dbforge->drop_column($this->name, $index);
        }

        // $this->dbforge->modify_column($this->name, $this->oldFields);
    }
}
PHP;
}
