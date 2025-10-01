<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class MY_Seeder extends \CI_Migration
{
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
    $this->name = $this->str->normalizeName($this->name);
  }
}
