<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class MY_Alter extends \CI_Migration
{
  /**
   * Array table fields.
   * 
   * @param array $fields
   */
  protected $fields = [];

  /**
   * Array table old fields. For the purpose of rolling back a migration.
   * 
   * @param array $oldFields
   */
  protected $oldFields = [];

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
    $this->prepareFields();
  }

  /**
   * Rollback migration.
   * 
   * @return void
   */
  public function down()
  {
    $this->prepareFields();
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
    $this->oldFields = $this->str->normalizeArrayField($this->oldFields);
  }
}
