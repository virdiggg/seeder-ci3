<?php

/**
 * Resolve Composer project root.
 *
 * Supports:
 * - ROOT/composer.json
 * - ROOT/application/composer.json
 */
$dir = realpath(__DIR__);
$composerRoot = null;

while ($dir && dirname($dir) !== $dir) {
  if (
    is_file($dir . DIRECTORY_SEPARATOR . 'composer.json') &&
    is_dir($dir . DIRECTORY_SEPARATOR . 'vendor')
  ) {
    $composerRoot = $dir;
    break;
  }

  $dir = dirname($dir);
}

if ($composerRoot === null) {
  throw new RuntimeException(
    'Unable to locate Composer project root.'
  );
}

// Determine CI3 project paths.
if (basename($composerRoot) === 'application') {
  $ROOT = dirname($composerRoot);
  $APPPATH = $composerRoot . DIRECTORY_SEPARATOR;
} else {
  $ROOT = $composerRoot;
  $APPPATH = $ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR;
}

$FCPATH = $ROOT . DIRECTORY_SEPARATOR;

// Validate application directory.
if (!is_dir($APPPATH)) {
  throw new RuntimeException(
    'CodeIgniter application directory not found: ' . $APPPATH
  );
}

// Package templates.
$packagePath = realpath(__DIR__);

$separator = DIRECTORY_SEPARATOR;

$copyFile = function ($source, $destination, $message) {
  if (!file_exists($source)) {
    echo "Source file not found: {$source}\n";
    return false;
  }

  if (!file_exists($destination)) {
    if (copy($source, $destination)) {
      echo "{$message}\n";
      return true;
    }

    echo "Failed to copy: {$destination}\n";
    return false;
  }

  echo basename($destination) . " already exists\n";
  return true;
};

/**
 * App_ci3 controller
 */
$appPath = $APPPATH . 'controllers' . $separator . 'App_ci3.php';
$templatePath = $packagePath . $separator . 'App_ci3.php';

if (!file_exists($appPath)) {
  $copyFile(
    $templatePath,
    $appPath,
    'App_ci3.php created'
  );
} else {
  $content = file_get_contents($appPath);

  if (
    strpos(
      $content,
      'Virdiggg\\SeederCi3\\MY_AppController'
    ) === false
  ) {
    $updated = preg_replace(
      '/class\s+App_ci3\s+extends\s+CI_AppController/',
      "use Virdiggg\\SeederCi3\\MY_AppController;\n\nclass App_ci3 extends MY_AppController",
      $content,
      1,
      $count
    );

    if ($count > 0 && $updated !== null) {
      file_put_contents($appPath, $updated);
      echo "App_ci3.php updated\n";
    } else {
      echo "App_ci3.php class declaration not found\n";
    }
  } else {
    echo "App_ci3.php already updated\n";
  }
}

/**
 * Seeder configuration
 */
$configPath = $APPPATH . 'config' . $separator . 'seeder.php';
$seederPath = $packagePath . $separator . 'seeder.php';

$copyFile(
  $seederPath,
  $configPath,
  'Seeder config created'
);

/**
 * Template views and helper
 */
$viewPath = $APPPATH . 'views';

$layoutsPath = $viewPath . $separator . 'layouts';

if (!is_dir($layoutsPath)) {
  $old = umask(0);
  mkdir($layoutsPath, 0755, true);
  umask($old);

  echo "Layouts directory created\n";
} else {
  $old = umask(0);
  @chmod($layoutsPath, 0755);
  umask($old);
}

$templateWrapperPath = $layoutsPath . $separator . 'wrapper.php';
$templateExamplePath = $viewPath . $separator . 'example.php';
$templateHelperPath = $APPPATH . 'helpers' . $separator . 'template_helper.php';

$wrapperPath = $packagePath . $separator . 'wrapper.php';
$examplePath = $packagePath . $separator . 'example.php';
$helperPath = $packagePath . $separator . 'template_helper.php';

$copyFile(
  $wrapperPath,
  $templateWrapperPath,
  'Template wrapper created'
);

$copyFile(
  $examplePath,
  $templateExamplePath,
  'Example view created'
);

$copyFile(
  $helperPath,
  $templateHelperPath,
  'Template helper created'
);

/**
 * Migration hooks
 */
$hooksPath = $APPPATH . 'hooks';

$postMigrationPath = $hooksPath . $separator . 'PostMigration.php';
$preMigrationPath = $hooksPath . $separator . 'PreMigration.php';

$postMigration = $packagePath . $separator . 'PostMigration.php';
$preMigration = $packagePath . $separator . 'PreMigration.php';

$copyFile(
  $preMigration,
  $preMigrationPath,
  'Pre Migration hook created'
);

$copyFile(
  $postMigration,
  $postMigrationPath,
  'Post Migration hook created'
);

/**
 * CLI executable
 */
$ci3Path = $FCPATH . 'ci3';
$cliSource = $packagePath . $separator . 'ci3';

if ($copyFile($cliSource, $ci3Path, 'CLI instance created')) {
  @chmod($ci3Path, 0755);
}

echo "\n";
echo "Composer root: {$composerRoot}\n";
echo "Project root: {$FCPATH}\n";
echo "Application path: {$APPPATH}\n\n";

echo "Seeder CI3 installed\n";
echo "Please run 'php ci3 init' in case of updates in config or CLI instance\n";
