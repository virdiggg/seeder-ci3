<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Utils\Str;

class MY_Seeder extends \CI_Migration
{
  /**
   * Table name.
   * 
   * @param string $tableName
   */
  protected $tableName;

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
    $this->prepareName();
  }

  /**
   * Rollback migration.
   * 
   * @return void
   */
  public function down()
  {
    $this->prepareName();
  }

  /**
   * Preparing table name.
   * 
   * @return string $this
   */
  protected function prepareName()
  {
    $this->tableName = $this->str->normalizeName($this->tableName);
  }
}
