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
      $this->CI->load->library('migration');

      if (!$this->CI->migration->current()) {
        throw new \Exception($this->CI->migration->error_string());
      }

      $res = $this->CI->db->select('version')->from('migrations')->get()->row();
      echo $this->str->greenText('Migrate number ' . $res->version . ' success');
      return;
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to migrate: ' . $th->getMessage());
      return;
    }
  }
}
