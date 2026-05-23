<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\{File, Str};

class InitCommand extends Command
{
  protected $input;
  protected $fl;
  protected $str;
  protected $env;

  public function __construct($input, $env)
  {
    $this->input = $input;
    $this->env = $env;

    $this->fl = new File();
    $this->str = new Str();
  }

  public function handle()
  {
    try {
      $this->publishAppController();
      $this->publishCli();
      $this->publishConfig();

      echo $this->str->greenText("Seeder CI3 initialized");
    } catch (\Throwable $th) {
      echo $this->str->redText('Seeder CI3 initialization failed: ' . $th->getMessage());
      return;
    }
  }

  private function publishAppController()
  {
    $appPath = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'App.php';

    $templatePath = APPPATH . 'vendor' . DIRECTORY_SEPARATOR . 'virdiggg' . DIRECTORY_SEPARATOR
      . 'seeder-ci3' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'App.php';

    if (!file_exists($appPath)) {
      copy($templatePath, $appPath);
      echo $this->str->greenText("Successfully created: " . $appPath);
      return;
    }

    $content = file_get_contents($appPath);

    if (strpos($content, 'Virdiggg\\SeederCi3\\MY_AppController') !== false) {
      return;
    }

    $content = preg_replace(
      '/class\s+App\s+extends\s+CI_AppController/',
      "use Virdiggg\\SeederCi3\\MY_AppController;\n\nclass App extends MY_AppController",
      $content
    );

    file_put_contents($appPath, $content);

    echo $this->str->greenText("Successfully updated: " . $appPath);
  }

  private function publishCli()
  {
    $target = FCPATH . 'ci3';

    $source = APPPATH . 'vendor' . DIRECTORY_SEPARATOR . 'virdiggg' . DIRECTORY_SEPARATOR
      . 'seeder-ci3' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'ci3';

    $shouldCopy = true;

    if (file_exists($target)) {
      $old = md5_file($target);
      $new = md5_file($source);

      if ($old === $new) {
        $shouldCopy = false;
      }
    }

    if ($shouldCopy) {
      copy($source, $target);
      @chmod($target, 0755);
      echo $this->str->greenText("Successfully created: " . $target);
    }
  }

  private function publishConfig()
  {
    $defaultConfigFile = APPPATH . 'config' . DIRECTORY_SEPARATOR . 'seeder.php';
    $envConfigFile = APPPATH . 'config' . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . 'seeder.php';

    if (file_exists($envConfigFile)) {
      $target = $envConfigFile;
    } elseif (file_exists($defaultConfigFile)) {
      $target = $defaultConfigFile;
    } else {
      $target = null;
    }

    $defaultConfig = SEEDER_ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'seeder.php';

    if (!file_exists($target)) {
      file_put_contents($target, $defaultConfig);
      echo $this->str->greenText("Successfully created: " . $target);
      return;
    }

    include $target;

    $updated = false;

    $required = [
      'allow_rollback' => "ENVIRONMENT !== 'production'",
      'migration_type' => "'timestamp'",
      'migration_path' => "APPPATH . 'migrations' . DIRECTORY_SEPARATOR",
      'date_time' => '[]',
      'db_conn' => "'default'",
      'limit_seed' => '10',
      'constructors' => '[]',
    ];

    $content = file_get_contents($target);

    foreach ($required as $key => $default) {
      if (strpos($content, "\$config['{$key}']") === false) {
        $content .= PHP_EOL . "\$config['{$key}'] = {$default};" . PHP_EOL;
        $updated = true;
      }
    }

    if ($updated) {
      file_put_contents($target, $content);
      echo $this->str->greenText("Successfully adjusted: " . $target);
    }
  }
}
