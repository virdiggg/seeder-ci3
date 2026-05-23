<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\Str;

class VersionCommand extends Command
{
  protected $input;
  protected $env;
  protected $str;

  public function __construct($input, $env, $e)
  {
    $this->input = $input;
    $this->env = $env;
    $this->str = new Str();
  }

  public function handle()
  {
    try {
      echo 'Seeder CI3 Version ' . $this->str->greenText($this->env->getVersion(), false);
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to retrieve version: ' . $th->getMessage());
      return;
    }
  }
}
