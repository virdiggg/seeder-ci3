<?php

namespace Virdiggg\SeederCI3\Console;

use Virdiggg\SeederCi3\Commands\{
  MakeModelCommand,
  MakeControllerCommand,
  MakeMigrationCommand,
  MakeSeederCommand,
  MakeFakerCommand,
  InitCommand,
  MigrateCommand,
};
use Virdiggg\SeederCi3\Console\Input;

class Kernel
{
  protected $env;
  protected $commands = [
    'init' => InitCommand::class,
    'migrate' => MigrateCommand::class,
    'make:model' => MakeModelCommand::class,
    'make:controller' => MakeControllerCommand::class,
    'make:migration' => MakeMigrationCommand::class,
    'make:seeder' => MakeSeederCommand::class,
    'make:faker' => MakeFakerCommand::class,
  ];

  public function __construct($env)
  {
    $this->env = $env;
  }

  public function handle(array $argv)
  {
    $commandName = $argv[3] ?? null;

    if (!$commandName) {
      echo "Command required\n";
      return;
    }

    $this->run($commandName, array_slice($argv, 4));
  }

  public function run($commandName, $arguments = [])
  {
    if (!isset($this->commands[$commandName])) {
      echo 'Unknown command: ' . $commandName . "\n";
      return;
    }

    $class = $this->commands[$commandName];
    $input = new Input($arguments);
    $command = new $class($input, $this->env, $this);
    $command->handle();
  }
}
