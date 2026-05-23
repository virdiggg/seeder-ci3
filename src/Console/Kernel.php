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
  RollbackCommand,
  TidyCommand,
  RouterCommand,
  VersionCommand,
};
use Virdiggg\SeederCi3\Console\Input;
use Virdiggg\SeederCi3\Utils\Str;

class Kernel
{
  protected $env;
  protected $commands = [
    'help' => [
      'class' => null,
      'description' => 'Display this help message',
      'example' => 'php ci3 help',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'version' => [
      'class' => VersionCommand::class,
      'description' => 'Display current Seeder CI3 version',
      'example' => 'php ci3 version',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'init' => [
      'class' => InitCommand::class,
      'description' => 'Initialize Seeder CI3 in your CodeIgniter 3 project',
      'example' => 'php ci3 init',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'publish' => [
      'class' => InitCommand::class,
      'description' => 'Alias for init command (deprecated)',
      'example' => 'php ci3 publish',
      'deprecated' => true,
      'deprecated_message' => 'Please use "php ci3 init" instead',
    ],
    'tidy' => [
      'class' => TidyCommand::class,
      'description' => 'Move all migration files inside "migrated" folder',
      'example' => 'php ci3 tidy',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'migrate' => [
      'class' => MigrateCommand::class,
      'description' => 'Run database migrations',
      'example' => 'php ci3 migrate',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'rollback' => [
      'class' => RollbackCommand::class,
      'description' => 'Rollback the last database migration',
      'example' => 'php ci3 rollback [--db=connection_name] [--to==version]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    // 'router' => [
    //   'class' => RouterCommand::class,
    //   'description' => 'Generate router files',
    //   'example' => 'php ci3 router:list [--postman]',
    //   'deprecated' => false,
    //   'deprecated_message' => '',
    // ],
    'make:model' => [
      'class' => MakeModelCommand::class,
      'description' => 'Create a new model',
      'example' => 'php ci3 make:model dir/model_name [--r] [--c] [--m] [--soft-delete] [--faker]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'make:controller' => [
      'class' => MakeControllerCommand::class,
      'description' => 'Create a new controller',
      'example' => 'php ci3 make:controller dir/controller_name [--r]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'make:migration' => [
      'class' => MakeMigrationCommand::class,
      'description' => 'Create a new migration file',
      'example' => 'php ci3 make:migration table_name [--soft-delete]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'make:seeder' => [
      'class' => MakeSeederCommand::class,
      'description' => 'Create a new seeder file',
      'example' => 'php ci3 make:seeder table_name [--limit=number]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
    'make:faker' => [
      'class' => MakeFakerCommand::class,
      'description' => 'Create a new faker file',
      'example' => 'php ci3 make:faker table_name [--limit=number]',
      'deprecated' => false,
      'deprecated_message' => '',
    ],
  ];
  protected $str;

  public function __construct($env)
  {
    $this->env = $env;
    $this->str = new Str();
  }

  public function handle(array $argv)
  {
    $commandName = $argv[3] ?? null;

    if (!$commandName) {
      echo $this->str->redText('Command required. Available command: php ci3 help');
      return;
    }

    if ($commandName === 'help') {
      $this->help();
      return;
    }

    $this->run($commandName, array_slice($argv, 4));
  }

  public function run($commandName, $arguments = [])
  {
    if (!isset($this->commands[$commandName])) {
      echo $this->str->redText('Unknown command: ' . $commandName);
      return;
    }

    $class = $this->commands[$commandName];
    $className = $class['class'];
    if ($class['deprecated']) {
      echo $this->str->yellowText('WARNING: This command is deprecated. ' . $class['deprecated_message']);
    }

    $input = new Input($arguments);
    $command = new $className($input, $this->env, $this);
    $command->handle();
  }

  private function help()
  {
    echo PHP_EOL;
    echo "Seeder CI3 Command Line Tool";
    echo PHP_EOL;
    echo PHP_EOL;

    foreach ($this->commands as $name => $command) {

      echo $this->str->greenText($name, false);
      echo PHP_EOL;
      echo '  Description : ' . $command['description'];
      echo PHP_EOL;
      echo '  Example     : ' . $this->str->greenText($command['example'], false);

      if ($command['deprecated']) {
        echo $this->str->yellowText('  Deprecated  : YES');
        echo $this->str->yellowText('  Message     : ' . $command['deprecated_message']);
      }

      echo PHP_EOL;
    }
  }
}
