<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\Str;

class MigrateCommand extends Command
{
  protected $input;
  protected $str;
  protected $env;
  protected $CI;

  public function __construct($input, $env)
  {
    $this->input = $input;
    $this->env = $env;
    $this->str = new Str();
    $this->CI = &get_instance();
  }

  public function handle()
  {
    try {
      $this->runPreMigrationHooks();

      $this->CI->load->library('migration');

      if (!$this->CI->migration->current()) {
        throw new \Exception($this->CI->migration->error_string());
      }

      $res = $this->CI->db->select('version')->from('migrations')->get()->row();
      echo $this->str->greenText('Migrate number ' . $res->version . ' success');

      $this->runPostMigrationHooks();
      return;
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to migrate: ' . $th->getMessage());
      return;
    }
  }

  protected function runPreMigrationHooks()
  {
    $hookFile = APPPATH . 'hooks' . DIRECTORY_SEPARATOR . 'PreMigration.php';

    if (!file_exists($hookFile)) {
      return;
    }

    require_once $hookFile;

    if (!class_exists('PreMigration')) {
      return;
    }

    $hook = new \PreMigration();

    if (!method_exists($hook, 'handle')) {
      return;
    }

    $hook->handle();
  }

  protected function runPostMigrationHooks()
  {
    $hookFile = APPPATH . 'hooks' . DIRECTORY_SEPARATOR . 'PostMigration.php';

    if (!file_exists($hookFile)) {
      return;
    }

    require_once $hookFile;

    if (!class_exists('PostMigration')) {
      return;
    }

    $hook = new \PostMigration();

    if (!method_exists($hook, 'handle')) {
      return;
    }

    $hook->handle();
  }
}
