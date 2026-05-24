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
      $this->publishMigrationHooks();

      echo $this->str->greenText("Seeder CI3 initialized");
    } catch (\Throwable $th) {
      echo $this->str->redText('Seeder CI3 initialization failed: ' . $th->getMessage());
      return;
    }
  }

  private function publishAppController()
  {
    $appPath = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'App_ci3.php';

    $templatePath = APPPATH . 'vendor' . DIRECTORY_SEPARATOR . 'virdiggg' . DIRECTORY_SEPARATOR
      . 'seeder-ci3' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'App_ci3.php';

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
      '/class\s+App_ci3\s+extends\s+CI_AppController/',
      "use Virdiggg\\SeederCi3\\MY_AppController;\n\nclass App_ci3 extends MY_AppController",
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

  private function publishMigrationHooks()
  {
    $hooksPath = APPPATH . 'hooks' . DIRECTORY_SEPARATOR;

    $this->fl->ensureDirectoryExists($hooksPath);

    $postMigrationPath = $hooksPath . 'PostMigration.php';
    $postMigration = SEEDER_ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'PostMigration.php';

    if (!file_exists($postMigrationPath)) {
      copy($postMigration, $postMigrationPath);
      echo "Post Migration hooks created\n";
    } else {
      echo "Post Migration hooks already exists\n";
    }

    $preMigrationPath = $hooksPath . 'PreMigration.php';
    $preMigration = SEEDER_ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'PreMigration.php';

    if (!file_exists($preMigrationPath)) {
      copy($preMigration, $preMigrationPath);
      echo "Pre Migration hooks created\n";
    } else {
      echo "Pre Migration hooks already exists\n";
    }

    $this->migrateLegacyHooks($postMigrationPath);
  }

  private function migrateLegacyHooks($postMigrationPath)
  {
    $legacyApp = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'App.php';

    if (!file_exists($legacyApp)) {
      return;
    }

    $content = file_get_contents($legacyApp);

    if (!$content) {
      return;
    }

    /**
     * Match:
     * if ($this->migrateCalled) {
     *     ...
     * }
     */
    preg_match(
      '/if\s*\(\s*\$this->migrateCalled\s*\)\s*\{(.*?)\n\s*\}/s',
      $content,
      $matches
    );

    if (!isset($matches[1])) {
      return;
    }

    $legacyHookContent = trim($matches[1]);

    if (!$legacyHookContent) {
      return;
    }

    /**
     * Convert $this to $CI
     */
    $replacements = [
      '$this->db' => '$CI->db',
      '$this->load' => '$CI->load',
      '$this->logger' => '$CI->logger',
      '$this->config' => '$CI->config',
      '$this->session' => '$CI->session',
      '$this->input' => '$CI->input',
    ];

    $legacyHookContent = str_replace(
      array_keys($replacements),
      array_values($replacements),
      $legacyHookContent
    );

    $templateContent = file_get_contents($postMigrationPath);

    if (!$templateContent) {
      return;
    }

    /**
     * Prevent duplicate migration
     */
    if (strpos($templateContent, $legacyHookContent) !== false) {
      return;
    }

    /**
     * Inject below:
     * $CI = &get_instance();
     */
    $injectedContent =
      PHP_EOL
      . PHP_EOL
      . '        // Migrated from legacy App::__destruct()'
      . PHP_EOL
      . preg_replace(
        '/^/m',
        '        ',
        $legacyHookContent
      );

    $templateContent = preg_replace(
      '/\$CI\s*=\s*&get_instance\(\);/',
      '$0' . $injectedContent,
      $templateContent,
      1
    );

    file_put_contents($postMigrationPath, $templateContent);

    echo $this->str->greenText("Legacy migration hooks migrated to PostMigration.php");
  }
}
