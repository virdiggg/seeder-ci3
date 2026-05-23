<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\{File, Str};

class TidyCommand extends Command
{
  protected $input;
  protected $fl;
  protected $str;
  protected $env;
  protected $config;

  public function __construct($input, $env,$e)
  {
    $this->input = $input;
    $this->env = $env;

    $this->fl = new File();
    $this->str = new Str();
    $this->config = $env->getConfig();
  }

  public function handle()
  {
    try {
      $this->fl->tidyingFiles($this->config->migrationPath);

      echo $this->str->greenText('All migration files have been tidied into "migrated" folder');
    } catch (\Throwable $th) {
      echo $this->str->redText('Tidying migration files failed: ' . $th->getMessage());
      return;
    }
  }
}
