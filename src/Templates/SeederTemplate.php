<?php

namespace Virdiggg\SeederCi3\Templates;

class SeederTemplate
{
    private $conn;
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
     * @param array  $dateTime
     */
    public function __construct($conn = 'default', $dateTime = [])
    {
        $this->conn = $conn;
        $this->addDateTime($dateTime);
    }

    /**
     * Parse input as printable string for seeder file.
     *
     * @param string $name
     * @param string $rand         Random string
     * @param array  $results
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return string
     */
    public function template($name, $rand, $results, $constructors = [])
    {
        $res = $this->parse($results);
        $keys = $res['keys'];
        $results = $res['results'];

        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class Migration_Seeder_' . $name . '_' . $rand . ' extends CI_Migration {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * DB Connection name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param object ${{conn}}' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private ${{conn}};' . PHP_EOL . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Table name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $name' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $name;' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        $this->{{conn}} = $this->load->database(\'{{conn}}\', TRUE);' . PHP_EOL;
        $print .= '        $this->name = \'' . $name . '\';' . PHP_EOL;
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
        $print .= '        $param = [];' . PHP_EOL . PHP_EOL;
        foreach ($results as $res) {
            $print .= $this->row($res, $keys);
        }
        $print .= PHP_EOL;
        $print .= '        $chunk = array_chunk($param, 10000);' . PHP_EOL;
        $print .= '        foreach ($chunk as $c) {' . PHP_EOL;
        $print .= '            $this->{{conn}}->insert_batch($this->name, $c);' . PHP_EOL;
        $print .= '        }' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Rollback migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function down() {' . PHP_EOL;
        $print .= '        $this->{{conn}}->truncate($this->name);' . PHP_EOL;
        $print .= '    }' . PHP_EOL; // end public function down()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Preparing array keys.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array $this' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function handleFields() {' . PHP_EOL;
        $print .= '        $this->name = strip_tags(trim(preg_replace(\'/\xc2\xa0/\', \'\', $this->name)));' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function handleFields()
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
    private function row($res, $keys) {
        $print = '        $param[] = [' . PHP_EOL;
        foreach ($keys as $k) {
            // Replace ALL ' (single quote) with " (double quote) from the value.
            $r = !is_null($res[$k]) ? $this->db->escape($res[$k]) : "NULL";

            // For DateTime value
            if (in_array($k, $this->dateTime) && $r !== "NULL") {
                $r = 'date("Y-m-d H:i:s.", strtotime('.$r.')).gettimeofday()["usec"]';
            }
            // Trim values
            $r = trim(strip_tags($r));
            // Delete whitespace and newline
            $r = str_replace(["\t", "\r", "\n", "\\"], '', $r);
            $print .= '            \'' . $k . '\' => ' . $r . ',' . PHP_EOL;
        }
        $print .= '        ];' . PHP_EOL; // end $param[]

        return $print;
    }

    /**
     * Setup query results.
     *
     * @param array $results
     *
     * @return void
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
}