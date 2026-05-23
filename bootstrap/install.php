<?php

$appPath = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'App.php';
$templatePath = __DIR__ . DIRECTORY_SEPARATOR . 'App.php';

if (!file_exists($appPath)) {
  copy($templatePath, $appPath);
  echo "App.php created\n";
} else {
  $content = file_get_contents($appPath);

  if (strpos($content, 'Virdiggg\\SeederCi3\\MY_AppController') === false) {
    $content = preg_replace(
      '/class\s+App\s+extends\s+CI_AppController/',
      "use Virdiggg\\SeederCi3\\MY_AppController;\n\nclass App extends MY_AppController",
      $content
    );

    file_put_contents($appPath, $content);
    echo "App.php updated\n";
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

$ci3Path = FCPATH . 'ci3';

copy(__DIR__ . DIRECTORY_SEPARATOR . 'ci3', $ci3Path);
@chmod($ci3Path, 0755);
echo "CLI instance created\n\n";

echo "Seeder CI3 installed\n";
echo "Please run 'php ci3 init' in case of updates in config or CLI instance\n";
