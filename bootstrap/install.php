<?php

$appPath = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'App_ci3.php';
$templatePath = 'vendor/virdiggg/seeder-ci3/example/App_ci3.php';

if (!file_exists($appPath)) {
  copy($templatePath, $appPath);
  echo "App_ci3.php created\n";
} else {
  $content = file_get_contents($appPath);

  if (strpos($content, 'Virdiggg\\SeederCi3\\MY_AppController') === false) {
    $content = preg_replace(
      '/class\s+App_ci3\s+extends\s+CI_AppController/',
      "use Virdiggg\\SeederCi3\\MY_AppController;\n\nclass App_ci3 extends MY_AppController",
      $content
    );

    file_put_contents($appPath, $content);
    echo "App_ci3.php updated\n";
  }
}

$configPath = APPPATH . 'config' . DIRECTORY_SEPARATOR . 'seeder.php';
$seederPath = __DIR__ . DIRECTORY_SEPARATOR . 'seeder.php';

if (!file_exists($configPath)) {
  copy($seederPath, $configPath);
  echo "Seeder config created\n";
} else {
  echo "Seeder config already exists\n";
}

$postMigrationPath = APPPATH . 'hooks' . DIRECTORY_SEPARATOR . 'PostMigration.php';
$preMigrationPath = APPPATH . 'hooks' . DIRECTORY_SEPARATOR . 'PreMigration.php';
$postMigration = __DIR__ . DIRECTORY_SEPARATOR . 'PostMigration.php';
$preMigration = __DIR__ . DIRECTORY_SEPARATOR . 'PreMigration.php';

if (!file_exists($preMigrationPath)) {
  copy($preMigration, $preMigrationPath);
  echo "Pre Migration hooks created\n";
} else {
  echo "Pre Migration hooks already exists\n";
}

if (!file_exists($postMigrationPath)) {
  copy($postMigration, $postMigrationPath);
  echo "Post Migration hooks created\n";
} else {
  echo "Post Migration hooks already exists\n";
}

$ci3Path = FCPATH . 'ci3';

copy(__DIR__ . DIRECTORY_SEPARATOR . 'ci3', $ci3Path);
@chmod($ci3Path, 0755);
echo "CLI instance created\n\n";

echo "Seeder CI3 installed\n";
echo "Please run 'php ci3 init' in case of updates in config or CLI instance\n";
