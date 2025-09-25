<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Type of migration, sequential or timestamp. Default to 'timestamp'.
 * 
 * Optional, we will take the value from migration.php if not present
 */
$config['migration_type'] = 'timestamp';

/**
 * Path of migration file. Default to 'ROOT/application/migrations'.
 * 
 * Optional, we will take the value from migration.php if not present
 */
$config['migration_path'] = APPPATH . 'migrations' . DIRECTORY_SEPARATOR;

/**
 * List of additional table rows with datetime data type.
 * 
 * Default to "['created_at', 'updated_at', 'approved_at', 'deleted_at']".
 */
$config['date_time'] = [];

/**
 * Name of database connection. Default to 'default'.
 */
$config['db_conn'] = 'default';

/**
 * List of additional function to be called in constructor. Default to [].
 */
$config['constructors'] = [
  'controller' => [
    '$this->authenticated->isAuthenticated();',
  ],
  'model' => [
    '$this->load->helper("string");',
  ],
  'seed' => [
    '$this->load->helper("string");',
  ],
  'migration' => [
    '$this->load->helper("string");',
  ],
];
