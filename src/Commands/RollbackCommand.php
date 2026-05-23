<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Utils\Str;

class RollbackCommand extends Command
{
  protected $input;
  protected $str;
  protected $env;
  protected $CI;
  protected $config;

  public function __construct($input, $env)
  {
    $this->input = $input;
    $this->env = $env;
    $this->str = new Str();
    $this->config = $env->getConfig();
    $this->CI = &get_instance();
  }

  public function handle()
  {
    try {
      $this->CI->load->library('migration');

      $dbConn = $this->input->option('db') ?? $this->config->dbConn;

      $resOld = $this->CI->db->select('version')->from('migrations')->get()->row();
      if (!isset($resOld->version)) {
        throw new \Exception('No migration found');
      }

      $currentVersion = $resOld->version;

      // Get all migration files
      $files = glob($this->config->migrationPath . '*.php');

      // If not found, or only found one file, then we can't do rollback
      if (!$files || count($files) <= 1) {
        throw new \Exception('No previous migration available');
      }

      // Filter files with version less than current version
      $rollbackFiles = [];
      foreach ($files as $file) {
        preg_match('/^([0-9]+)/', basename($file), $matches);
        $fileVersion = (int) ($matches[1] ?? 0);

        if ($fileVersion < $currentVersion) {
          $rollbackFiles[] = [
            'version' => $fileVersion,
            'file' => $file,
          ];
        }
      }

      // Sort by version descending/latest version first
      usort($rollbackFiles, function ($a, $b) {
        return $b['version'] <=> $a['version'];
      });

      $to = $this->input->option('to');

      $target = null;
      if ($to) {
        // If 'to' option is provided, find the migration file with version or name that matches 'to'
        foreach ($rollbackFiles as $item) {
          $fileName = basename($item['file']);
          if (preg_match('/^' . preg_quote($to, '/') . '_/', $fileName)) {
            $target = $item['file'];
            break;
          }
        }

        if (!$target) {
          throw new \Exception("Migration not found: {$to}");
        }
      } else {
        // Else, just take the latest migration file with version less than current version
        if (count($rollbackFiles) === 0) {
          throw new \Exception('No previous migration available');
        }

        $target = $rollbackFiles[0]['file'];
      }

      // Extract version from target file name
      preg_match('/^([0-9]+)/', basename($target), $matches);

      if (!isset($matches[1])) {
        throw new \Exception('Unable to parse migration version');
      }

      $version = (int) $matches[1];
      if (!$this->CI->migration->version($version)) {
        throw new \Exception($this->CI->migration->error_string());
      }

      $res = $this->CI->db->select('version')->from('migrations')->get()->row();
      echo $this->str->greenText('Rollback to number ' . $res->version . ' success');
      return;
    } catch (\Throwable $th) {
      echo $this->str->redText('Failed to rollback: ' . $th->getMessage());
      return;
    }
  }
}
