<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class MY_Migration extends \CI_Migration
{
  /**
   * Array table fields.
   * 
   * @param array $fields
   */
  protected $fields = [];

  /**
   * Primary key.
   * 
   * @param array $primary
   */
  protected $primary = 'id';

  /**
   * Table name.
   * 
   * @param string $name
   */
  protected $name;

  private $str;

  public function __construct()
  {
    parent::__construct();
    $this->str = new Str();
  }

  /**
   * Migration.
   * 
   * @return void
   */
  public function up()
  {
    if (empty($this->name) || empty($this->fields)) {
      print("MIGRATION ERROR: " . $this->str->redText('table and fields should not be empty'));
      return;
    }

    $this->prepareFields();

    if ($this->db->table_exists($this->name)) {
      $this->dbforge->drop_table($this->name, TRUE);
    }

    $this->dbforge->add_field($this->fields);
    $this->dbforge->add_key($this->primary, TRUE);
    $this->dbforge->create_table($this->name);
  }

  /**
   * Rollback migration.
   * 
   * @return void
   */
  public function down()
  {
    if (empty($this->name)) {
      print("MIGRATION ERROR: " . $this->str->redText('table should not be empty'));
      return;
    }

    $this->prepareName();

    $this->dbforge->drop_table($this->name, TRUE);
  }

  /**
   * Preparing table name.
   * 
   * @return string $this
   */
  protected function prepareName()
  {
    $this->name = strip_tags(trim(preg_replace('/\xc2\xa0/', '', $this->name)));
  }

  /**
   * Preparing array keys.
   * 
   * @return array $this
   */
  protected function prepareFields()
  {
    $this->prepareName();
    $this->fields = $this->str->normalizeArrayField($this->fields);
  }
}
