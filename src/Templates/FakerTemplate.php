<?php

namespace Virdiggg\SeederCi3\Templates;

use Virdiggg\SeederCi3\Utils\Str;

class FakerTemplate
{
    private $conn;
    private $driver;
    private $str;

    /**
     * Constructor.
     * 
     * @param string $conn
     * @param string $driver
     */
    public function __construct($conn = 'default', $driver = 'mysqli')
    {
        $this->conn = $conn;
        $this->driver = $driver;
        $this->str = new Str();
    }

    /**
     * Parse input as printable string for seeder file.
     *
     * @param array  $fields
     * @param string $name
     * @param string $rand         Random string
     * @param array  $param
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return string
     */
    public function template($fields, $name, $rand, $param, $constructors = [])
    {
        $limit = 10;
        if (count($param) > 0) {
            if (in_array('--limit', $param)) {
                $limit = (int) $param['--limit'];
            }
        }

        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'use Virdiggg\SeederCi3\MY_Seeder;' . PHP_EOL;
        $print .= 'use Faker\Factory as FakerFactory;' . PHP_EOL . PHP_EOL;
        $print .= 'Class Migration_Faker_' . ucwords($name) . '_' . $rand . ' extends MY_Seeder {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * DB Connection name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param object ${{conn}}' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private ${{conn}};' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        /**' . PHP_EOL;
        $print .= '         * Table name.' . PHP_EOL;
        $print .= '         * ' . PHP_EOL;
        $print .= '         * @param string $name' . PHP_EOL;
        $print .= '         */' . PHP_EOL;
        $print .= '        $this->name = \'' . $name . '\';' . PHP_EOL;
        $print .= '        $this->{{conn}} = $this->load->database(\'{{conn}}\', TRUE);' . PHP_EOL;
        foreach ($constructors as $constructor) {
            $print .= '        ' . $constructor . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Run migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function up() {' . PHP_EOL;
        $print .= '        parent::up();' . PHP_EOL;
        $print .= '        $faker = FakerFactory::create();' . PHP_EOL;
        $print .= '        for ($i = 0; $i < ' . $limit . '; $i++) {' . PHP_EOL;
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
        if ($limit > 10000) {
            $print .= '        $chunk = array_chunk($param, 10000);' . PHP_EOL;
            $print .= '        foreach ($chunk as $c) {' . PHP_EOL;
            $print .= '            $this->{{conn}}->insert_batch($this->name, $c);' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
        } else {
            $print .= '        $this->{{conn}}->insert_batch($this->name, $param);' . PHP_EOL;
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
            $print .= '        $this->{{conn}}->query("TRUNCATE TABLE " + $this->name + " RESTART IDENTITY");' . PHP_EOL;
        } else {
            $print .= '        $this->{{conn}}->truncate($this->name);' . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL; // end public function down()
        $print .= '}'; // end class

        return str_replace('{{conn}}', $this->conn, $print);
    }

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

    private function fakeExpression($field) {
        $name = strtolower($field->name);

        if (strpos($name, 'email') !== false) {
            return '$faker->email';
        }

        if (strpos($name, 'name') !== false) {
            return '$faker->name';
        }

        if (strpos($name, 'phone') !== false) {
            return '$faker->phoneNumber';
        }

        if (strpos($name, 'address') !== false) {
            return '$faker->address';
        }

        if (strpos($name, 'city') !== false) {
            return '$faker->city';
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
                return '$faker->paragraph';

            default:
                return '$faker->word';
        }
    }
}