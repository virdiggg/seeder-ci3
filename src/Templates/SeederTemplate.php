<?php

namespace Virdiggg\SeederCi3\Templates;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class SeederTemplate
{
  private $conn;
  private $driver;
  private $str;

  /**
   * Date time fields.
   *
   * @param array
   */
  public $dateTime = ['created_at', 'updated_at', 'approved_at', 'deleted_at'];

  /**
   * Constructor.
   * 
   * @param string $conn
   * @param string $driver
   * @param array  $dateTime
   */
  public function __construct($conn = 'default', $driver = 'mysqli', $dateTime = [])
  {
    $this->conn = $conn;
    $this->driver = $driver;
    $this->addDateTime($dateTime);
    $this->str = new Str();
  }

  /**
   * Parse input as printable string for seeder file.
   *
   * @param string $name
   * @param string $tableName
   * @param array  $data
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function template($name, $tableName, $data, $param, $constructors = [])
  {
    return $this->str->startsWith($name, 'Seeder_')
      ? $this->seederTable($name, $tableName, $data, $param, $constructors)
      : $this->fakerTable($name, $tableName, $data, $param, $constructors);
  }

  /**
   * Prepare seeder table template
   *
   * @param string $name
   * @param string $tableName
   * @param array  $results
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function seederTable($name, $tableName, $results, $param, $constructors = [])
  {
    $res = $this->parse($results);
    $keys = $res['keys'];
    $results = $res['results'];

    $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
    $print .= 'use Virdiggg\SeederCi3\MY_Seeder;' . PHP_EOL . PHP_EOL;
    $print .= 'Class Migration_' . $name . ' extends MY_Seeder {' . PHP_EOL;
    $print .= '    /**' . PHP_EOL;
    $print .= '     * DB Connection.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @param object ${{conn}}' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    private ${{conn}};' . PHP_EOL . PHP_EOL;
    $print .= '    public function __construct() {' . PHP_EOL;
    $print .= '        parent::__construct();' . PHP_EOL;
    foreach ($constructors as $constructor) {
      $print .= '        ' . $constructor . PHP_EOL;
    }
    $print .= '        $this->tableName = \'' . $tableName . '\';' . PHP_EOL;
    $print .= '        $this->{{conn}} = $this->load->database(\'{{conn}}\', TRUE);' . PHP_EOL;
    $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
    $print .= '    /**' . PHP_EOL;
    $print .= '     * Run migration.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @return void' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    public function up() {' . PHP_EOL;
    $print .= '        parent::up();' . PHP_EOL;
    $print .= '        $param = [];' . PHP_EOL . PHP_EOL;
    foreach ($results as $res) {
      $print .= $this->row($res, $keys);
    }
    $print .= PHP_EOL;
    if (count($results) > 10000) {
      $print .= '        $chunk = array_chunk($param, 10000);' . PHP_EOL;
      $print .= '        foreach ($chunk as $c) {' . PHP_EOL;
      $print .= '            $this->{{conn}}->insert_batch($this->tableName, $c);' . PHP_EOL;
      $print .= '        }' . PHP_EOL;
    } else {
      $print .= '        $this->{{conn}}->insert_batch($this->tableName, $param);' . PHP_EOL;
    }
    $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
    $print .= '    /**' . PHP_EOL;
    $print .= '     * Rollback migration.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @return void' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    public function down() {' . PHP_EOL;
    $print .= '        parent::down();' . PHP_EOL;
    if ($this->driver === 'postgre') {
      $print .= '        $this->{{conn}}->query("TRUNCATE TABLE " . $this->tableName . " RESTART IDENTITY");' . PHP_EOL;
    } else {
      $print .= '        $this->{{conn}}->truncate($this->tableName);' . PHP_EOL;
    }
    $print .= '    }' . PHP_EOL; // end public function down()
    $print .= '}'; // end class

    return str_replace('{{conn}}', $this->conn, $print);
  }

  /**
   * Prepare faker table template
   *
   * @param string $name
   * @param string $tableName
   * @param array  $fields
   * @param array  $param
   * @param array  $constructors List of additional function to be called in constructor.
   *
   * @return string
   */
  public function fakerTable($name, $tableName, $fields, $param, $constructors = [])
  {
    $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
    $print .= 'use Faker\Factory as FakerFactory;' . PHP_EOL;
    $print .= 'use Virdiggg\SeederCi3\MY_Seeder;' . PHP_EOL . PHP_EOL;
    $print .= 'Class Migration_' . $name . ' extends MY_Seeder {' . PHP_EOL;
    $print .= '    /**' . PHP_EOL;
    $print .= '     * DB Connection.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @param object ${{conn}}' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    private ${{conn}};' . PHP_EOL . PHP_EOL;
    $print .= '    /**' . PHP_EOL;
    $print .= '     * Faker instance.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @param object $faker' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    private $faker;' . PHP_EOL . PHP_EOL;
    $print .= '    public function __construct() {' . PHP_EOL;
    $print .= '        parent::__construct();' . PHP_EOL;
    foreach ($constructors as $constructor) {
      $print .= '        ' . $constructor . PHP_EOL;
    }
    $print .= '        $this->tableName = \'' . $tableName . '\';' . PHP_EOL;
    $print .= '        $this->{{conn}} = $this->load->database(\'{{conn}}\', TRUE);' . PHP_EOL;
    $print .= '        $this->faker = FakerFactory::create();' . PHP_EOL;
    $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
    $print .= '    /**' . PHP_EOL;
    $print .= '     * Run migration.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @return void' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    public function up() {' . PHP_EOL;
    $print .= '        parent::up();' . PHP_EOL;
    $print .= '        $param = [];' . PHP_EOL;
    $print .= '        for ($i = 0; $i < ' . $param['limit'] . '; $i++) {' . PHP_EOL;
    $print .= '            $param[] = [' . PHP_EOL;
    foreach ($fields as $field) {
      if ($this->shouldSkipField($field)) {
        continue;
      }

      $print .= "                '" . $field->name . "' => "
        . $this->fakeExpression($field)
        . "," . PHP_EOL;
    }
    $print .= '            ];' . PHP_EOL;
    $print .= '        }' . PHP_EOL;
    if ($param['limit'] > 10000) {
      $print .= '        $chunk = array_chunk($param, 10000);' . PHP_EOL;
      $print .= '        foreach ($chunk as $c) {' . PHP_EOL;
      $print .= '            $this->{{conn}}->insert_batch($this->tableName, $c);' . PHP_EOL;
      $print .= '        }' . PHP_EOL;
    } else {
      $print .= '        $this->{{conn}}->insert_batch($this->tableName, $param);' . PHP_EOL;
    }
    $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
    $print .= '    /**' . PHP_EOL;
    $print .= '     * Rollback migration.' . PHP_EOL;
    $print .= '     * ' . PHP_EOL;
    $print .= '     * @return void' . PHP_EOL;
    $print .= '     */' . PHP_EOL;
    $print .= '    public function down() {' . PHP_EOL;
    $print .= '        parent::down();' . PHP_EOL;
    if ($this->driver === 'postgre') {
      $print .= '        $this->{{conn}}->query("TRUNCATE TABLE " . $this->tableName . " RESTART IDENTITY");' . PHP_EOL;
    } else {
      $print .= '        $this->{{conn}}->truncate($this->tableName);' . PHP_EOL;
    }
    $print .= '    }' . PHP_EOL; // end public function down()
    $print .= '}'; // end class

    return str_replace('{{conn}}', $this->conn, $print);
  }

  /**
   * Add date time fields to current date time's array.
   *
   * @param array $fields
   *
   * @return void
   */
  private function addDateTime($fields = [])
  {
    $old = $this->dateTime;
    $this->dateTime = array_values(array_unique(array_merge($old, $fields)));
  }

  /**
   * Parse result.
   *
   * @return string
   */
  private function row($res, $keys)
  {
    $print = '        $param[] = [' . PHP_EOL;
    foreach ($keys as $k) {
      // Replace ALL ' (single quote) with " (double quote) from the value.
      $r = !is_null($res[$k]) ? $this->str->escape($res[$k]) : 'NULL';

      // For DateTime value
      if (in_array($k, $this->dateTime) && $r !== 'NULL') {
        $r = 'date("Y-m-d H:i:s.", strtotime(' . $r . ')).gettimeofday()["usec"]';
      }

      // Trim values
      $r = trim(strip_tags($r));
      // Delete whitespace and newline
      $r = str_replace(["\t", "\r", "\n", "\\"], '', $r);
      // Replace escape string in query to replace string in PHP
      $r = str_replace("''", "\\'", $r);
      $print .= '            "' . $k . '" => ' . $r . ',' . PHP_EOL;
    }
    $print .= '        ];' . PHP_EOL; // end $param[]

    return $print;
  }

  /**
   * Setup query results.
   *
   * @param array $results
   *
   * @return array
   */
  private function parse($results)
  {
    // Array keys for column name.
    $keys = array_keys($results[0]);

    // Reverse array to Descending.
    // We don't know which incremental value this table has
    // and which one should we use, so we do it manually.
    asort($results);

    // Rebase the array.
    $results = array_values($results);

    return [
      'results' => $results,
      'keys' => $keys
    ];
  }

  /**
   * Determine if the field should be skipped for faker generation.
   * 
   * @param object $field
   * 
   * @return bool
   */
  private function shouldSkipField($field)
  {
    $name = strtolower($field->name);

    /*
        |--------------------------------------------------------------------------
        | Primary Key
        |--------------------------------------------------------------------------
        */
    if (!empty($field->primary_key)) {
      return true;
    }

    /*
        |--------------------------------------------------------------------------
        | Common Auto Columns
        |--------------------------------------------------------------------------
        */
    $skip = [
      'id',
      'created_at',
      'updated_at',
      'deleted_at',
    ];

    if (in_array($name, $skip)) {
      return true;
    }

    /*
        |--------------------------------------------------------------------------
        | Common Limited Constraint Columns
        |--------------------------------------------------------------------------
        */
    if (strpos($name, 'otp') !== false || strpos($name, 'pin') !== false) {
      return true;
    }

    /*
        |--------------------------------------------------------------------------
        | UUID columns
        |--------------------------------------------------------------------------
        */
    if (strpos($name, 'uuid') !== false || $field->type == 'uuid') {
      return true;
    }

    /*
        |--------------------------------------------------------------------------
        | PostgreSQL generated UUID
        |--------------------------------------------------------------------------
        */
    if (!empty($field->default)) {
      $default = strtolower($field->default);

      if (
        strpos($default, 'gen_random_uuid') !== false ||
        strpos($default, 'uuid_generate_v4') !== false
      ) {
        return true;
      }
    }

    return false;
  }

  /**
   * Generate faker expression based on field name and type.
   * 
   * @param object $field
   * 
   * @return string
   */
  private function fakeExpression($field)
  {
    $name = strtolower($field->name);

    if (strpos($name, 'email') !== false) {
      return '$this->faker->email';
    }

    if (strpos($name, 'name') !== false) {
      return '$this->faker->name';
    }

    if (strpos($name, 'phone') !== false) {
      return '$this->faker->phoneNumber';
    }

    if (strpos($name, 'address') !== false) {
      return '$this->faker->address';
    }

    if (strpos($name, 'city') !== false) {
      return '$this->faker->city';
    }

    if (strpos($name, 'password') !== false) {
      return 'password_hash("password", PASSWORD_BCRYPT)';
    }

    if (strpos($name, 'date') !== false) {
      return 'date("Y-m-d")';
    }

    if (strpos($name, 'created_at') !== false) {
      return 'date("Y-m-d H:i:s")';
    }

    if (strpos($name, 'updated_at') !== false) {
      return 'date("Y-m-d H:i:s")';
    }

    switch ($field->type) {
      case 'int':
      case 'bigint':
        return 'rand(1, 9999)';

      case 'date':
        return 'date("Y-m-d")';

      case 'datetime':
      case 'timestamp':
        return 'date("Y-m-d H:i:s")';

      case 'text':
        return '$this->faker->paragraph';

      default:
        return '$this->faker->word';
    }
  }
}
