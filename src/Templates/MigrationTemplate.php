<?php

namespace Virdiggg\SeederCi3\Templates;

class MigrationTemplate
{
    private $driver;

    public function __construct($driver = 'mysql')
    {
        $this->driver = $driver;
    }

    /**
     * Parse input as printable string for migration file.
     *
     * @param string $name
     * @param string $rand
     * @param string $prefix
     * @param array  $param
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return string
     */
    public function template($name, $rand, $prefix, $param, $constructors = [])
    {
        $softDelete = $this->softDelete($param);

        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class Migration_' . ucwords($prefix) . '_' . $name . '_' . $rand . ' extends CI_Migration {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Array table fields.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param array $fields' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $fields;' . PHP_EOL . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Array table old fields. For the purpose of rolling back a migration.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $oldFields' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $oldFields;' . PHP_EOL . PHP_EOL;
        } else {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Primary key.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $primary' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $primary;' . PHP_EOL . PHP_EOL;
        }
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Table name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $name' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $name;' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        $this->name = \'' . $name . '\';' . PHP_EOL;
        if ($prefix !== 'alter') {
            $print .= '        $this->primary = \'id\';' . PHP_EOL;
        }
        $print .= '        $this->fields = [' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= "            // 'old_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'new_name', // if you want to modify its name" . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= "            // 'old_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'new_name', // if you want to modify its name" . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
        } else {
            $print .= '            $this->primary => [' . PHP_EOL;
            $print .= "                'type' => 'BIGINT'," . PHP_EOL;
            $print .= "                'unsigned' => TRUE," . PHP_EOL;
            $print .= "                'auto_increment' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'created_by' => [" . PHP_EOL;
            $print .= "                'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "                'constraint' => 50," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'created_at' => [" . PHP_EOL;
            $print .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'updated_by' => [" . PHP_EOL;
            $print .= "                'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "                'constraint' => 50," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'updated_at' => [" . PHP_EOL;
            $print .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
        }
        $print .= $softDelete;
        $print .= '        ];' . PHP_EOL . PHP_EOL; // end public $this->fields
        if ($prefix === 'alter') {
            $print .= '        $this->oldFields = [' . PHP_EOL;
            $print .= $softDelete;
            $print .= "            // 'new_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'old_name'," . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= "            // 'new_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'old_name'," . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= '        ];' . PHP_EOL . PHP_EOL; // end public $this->oldFields
        }
        $print .= '        // Handle keys of $this->fields' . ($prefix === 'alter' ? ' and $this->oldFields' : '') . '.' . PHP_EOL;
        $print .= '        // Convert special characters to underscore.' . PHP_EOL;
        $print .= '        $this->handleFields();' . PHP_EOL;
        foreach ($constructors as $constructor) {
            $print .= '        ' . $constructor . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function up() {' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '        $this->dbforge->add_column($this->name, $this->fields);' . PHP_EOL;
            $print .= '        // $this->dbforge->modify_column($this->name, $this->fields);' . PHP_EOL;
        } else {
            $print .= '        if ($this->db->table_exists($this->name)) {' . PHP_EOL;
            $print .= '            $this->dbforge->drop_table($this->name, TRUE);' . PHP_EOL;
            $print .= '        }' . PHP_EOL . PHP_EOL;
            $print .= '        $this->dbforge->add_key($this->primary, TRUE);' . PHP_EOL;
            $print .= '        $this->dbforge->create_table($this->name);' . PHP_EOL;
            if ($this->driver === 'postgre') {
                $print .= '        // Uncomment if you want to create index for this table.' . PHP_EOL;
                $print .= '        // Recommended if this table doesn\'t have UPDATE and DELETE operations. PostgreSQL only.' . PHP_EOL;
                $print .= '        // $this->db->query(\'CREATE INDEX CONCURRENTLY ON "\'.$this->name.\'" ("\'.join(\'", "\', array_keys($this->fields)).\'")\');' . PHP_EOL;
            }
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Rollback migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function down() {' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '        foreach ($this->fields as $index => $name) {' . PHP_EOL;
            $print .= '            $this->dbforge->drop_column($this->name, $index);' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
            $print .= '        // $this->dbforge->modify_column($this->name, $this->oldFields);' . PHP_EOL;
        } else {
            $print .= '        $this->dbforge->drop_table($this->name, TRUE);' . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function down()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Preparing array keys.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array $this' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function handleFields() {' . PHP_EOL;
        $print .= '        $this->name = strip_tags(trim(preg_replace(\'/\xc2\xa0/\', \'\', $this->name)));' . PHP_EOL;
        if ($prefix !== 'alter') {
            $print .= '        $this->primary = strip_tags(trim(preg_replace(\'/\xc2\xa0/\', \'\', $this->primary)));' . PHP_EOL;
        }
        $print .= '        $fields = $this->fields;' . PHP_EOL;
        $print .= '        $res = [];' . PHP_EOL;
        $print .= '        foreach ($fields as $key => $f) {' . PHP_EOL;
        $print .= '            $res[str_replace("\'", "", preg_replace(\'/[^a-zA-Z0-9\\\']/\', \'_\', trim($key)))] = $f;' . PHP_EOL;
        $print .= '        }' . PHP_EOL;
        $print .= '        $this->fields = $res;' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '        $oldFields = $this->oldFields;' . PHP_EOL;
            $print .= '        $res = [];' . PHP_EOL;
            $print .= '        foreach ($oldFields as $key => $f) {' . PHP_EOL;
            $print .= '            $res[str_replace("\'", "", preg_replace(\'/[^a-zA-Z0-9\\\']/\', \'_\', trim($key)))] = $f;' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
            $print .= '        $this->oldFields = $res;' . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function handleFields()
        $print .= '}'; // end class

        return $print;
    }

    /**
     * Param soft delete
     * 
     * @param string $name
     * 
     * @return string
     */
    private function softDelete($param) {
        if (!in_array('--soft-delete', $param)) {
            return '';
        }

        $softDelete = "            'deleted_by' => [" . PHP_EOL;
        $softDelete .= "                'type' => 'VARCHAR'," . PHP_EOL;
        $softDelete .= "                'constraint' => 50," . PHP_EOL;
        $softDelete .= "                'null' => TRUE," . PHP_EOL;
        $softDelete .= '            ],' . PHP_EOL;
        $softDelete .= "            'deleted_at' => [" . PHP_EOL;
        $softDelete .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
        $softDelete .= "                'null' => TRUE," . PHP_EOL;
        $softDelete .= '            ],' . PHP_EOL;

        return $softDelete;
    }
}