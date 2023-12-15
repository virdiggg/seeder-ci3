<?php 

namespace Virdiggg\SeederCi3;

defined('APPPATH') or define('APPPATH', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR);
defined('SEEDER_PATH') or define('SEEDER_PATH', APPPATH.'migrations');

class Seeder
{
    /**
     * Instance CI.
     *
     * @param object
     */
    private $CI;

    /**
     * DB Connection.
     *
     * @param object
     */
    private $db;

    /**
     * Custom DB setting.
     *
     * @param string
     */
    public $conn;

    /**
     * Date time fields.
     *
     * @param array
     */
    public $dateTime = ['created_at', 'updated_at', 'approved_at', 'deleted_at'];

    /**
     * Folder to save seeder file.
     *
     * @param string
     */
    public $path;

    /**
     * File Pointer.
     *
     * @param object
     */
    public $filePointer;

    /**
     * Emoticons.
     *
     * @param array
     */
    public $OwO;

    /**
     * Migration type. sequential or timestamp
     *
     * @param string
     */
    private $migrationType;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->OwO = [
            '╰(*°▽°*)╯', '(❁´◡`❁)', "(*/ω＼*)", '(^///^)', '☆*: .｡. o(≧▽≦)o .｡.:*☆', "(●'◡'●)", 'ヾ(≧▽≦*)o',
            'ψ(｀∇´)ψ', 'φ(*￣0￣)', '（￣︶￣）↗', 'q(≧▽≦q)', '*^____^*', '(～￣▽￣)～', '( •̀ ω •́ )✧', '[]~(￣▽￣)~*',
            'O(∩_∩)O', 'o(*^＠^*)o', 'φ(゜▽゜*)♪', '(*^▽^*)', "`(*>﹏<*)′", '(✿◡‿◡)', '(●ˇ∀ˇ●)', '(´▽`ʃ♡ƪ)', '(≧∇≦)ﾉ',
            '(*^_^*)', '（*＾-＾*）', '\^o^/', '(￣y▽￣)╭ Ohohoho.....', '○( ＾皿＾)っ Hehehe…', '(‾◡◝)', '(o゜▽゜)o☆',
            '(〃￣︶￣)人(￣︶￣〃)', '(^_-)db(-_^)', 'o(*￣▽￣*)ブ', 'o(*^▽^*)┛', '(≧∀≦)ゞ', '♪(^∇^*)', 'o(*￣▽￣*)ブ',
            '(oﾟvﾟ)ノ', 'o(*￣︶￣*)o', '( $ _ $ )', '(/≧▽≦)/', 'o(*≧▽≦)ツ┏━┓', 'ㄟ(≧◇≦)ㄏ', 'ヾ(＠⌒ー⌒＠)ノ', '(☆▽☆)',
            'ヾ(≧ ▽ ≦)ゝ', 'o((>ω< ))o', '( *︾▽︾)', '(((o(*ﾟ▽ﾟ*)o)))', '＼(((￣(￣(￣▽￣)￣)￣)))／', '( *^-^)ρ(^0^* )',
            '♪(´▽｀)', "~~~///(^v^)\\\\\\\~~~", 'o(*￣▽￣*)o', '(p≧w≦q)', 'ƪ(˘⌣˘)ʃ', '( •̀ ω •́ )y'
        ];
    }

    /**
     * Create a simple seeder file.
     *
     * @param string $fullName Table name
     * @param array  $param    Optional parameter
     *
     * @return void
     */
    public function seed($name = '', $param = [])
    {
        if (!$name) {
            print($this->parseRedText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // This is a different connection.
        // So don't be confused with the one we are going to print in seeder file.
        $this->db = $this->CI->load->database($this->getConn(), TRUE);

        if (!$this->db->table_exists($name)) {
            print($this->parseRedText('TABLE "' . $name . '" NOT FOUND IN YOUR DATABASE ╰(*°▽°*)╯'));
            return;
        }

        $results = $this->db->select()->from(trim($name))
            ->get()->result_array();

        if (count($results) === 0) {
            print($this->parseRedText('NO RECORDS IN TABLE "' . $name . '" ╰(*°▽°*)╯'));
            return;
        }

        $rand = $this->rand(4);

        // Parse input as printable string.
        $print = $this->parseInputSeeder($name, $rand, $results);

        // Get the latest migration file order.
        $count = $this->latest($this->getPath());

        $name = $this->parseFileName($count . '_seeder_' . $name . '_' . $rand);
        // Create seeder file.
        $this->createFile($this->getPath(), $name);

        // Write to newly created seeder file.
        fwrite($this->filePointer, $print . PHP_EOL);

        print('SEEDER CREATED: ' . $this->parseGreenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple migration file.
     *
     * @param string $fullName Table name
     * @param array  $param    Optional parameter
     *
     * @return void
     */
    public function migration($name = '', $param = [])
    {
        if (!$name) {
            print($this->parseRedText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // This is a different connection.
        // So don't be confused with the one we are going to print in migration file.
        $this->db = $this->CI->load->database($this->getConn(), TRUE);

        $prefix = 'create';
        if ($this->db->table_exists($name)) {
            $prefix = 'alter';
        }

        $rand = $this->rand(4);

        // Parse input as printable string.
        $print = $this->parseInputMigration($name, $rand, $prefix, $param);

        // Get the latest migration file order.
        $count = $this->latest($this->getPath());

        $name = $this->parseFileName($count . '_' . $prefix . '_' . $name . '_' . $rand);
        // Create migration file.
        $this->createFile($this->getPath(), $name);

        // Write to newly created migration file.
        fwrite($this->filePointer, $print . PHP_EOL);

        print('MIGRATION CREATED: ' . $this->parseGreenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple controller file.
     *
     * @param string $fullName Table name
     * @param array  $param    Optional parameter
     *
     * @return void
     */
    public function controller($fullName = '', $param = [])
    {
        if (!$fullName) {
            print($this->parseRedText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->beforeLast($fullName, '\\');
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to controllers folder
        $this->setPath(APPPATH . 'controllers' . $before);

        // File name is after the last slash \.
        $name = $this->afterLast($fullName, '\\');

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        $withResources = FALSE;
        if (count($param) > 0) {
            if (in_array('--r', $param)) {
                $withResources = TRUE;
            }
        }

        // Parse input as printable string.
        $print = $this->parseInputController($name, $withResources);

        $name = $this->parseFileName($name);
        // Create controller file.
        $this->createFile($this->getPath(), $name);

        // Write to newly created controller file.
        fwrite($this->filePointer, $print . PHP_EOL);

        print('CONTROLLER CREATED: ' . $this->parseGreenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple model file.
     *
     * @param string $fullName Table name
     * @param array  $param    Optional parameter
     *
     * @return void
     */
    public function model($fullName = '', $param = [])
    {
        if (!$fullName) {
            print($this->parseRedText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->beforeLast($fullName, '\\');
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to models folder
        $this->setPath(APPPATH . 'models' . $before);

        // File name is after the last slash \.
        $name = $this->afterLast($fullName, '\\');

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        $withResources = $withController = $withSoftDelete = FALSE;
        if (count($param) > 0) {
            if (in_array('--r', $param)) {
                $withResources = TRUE;
            }
            if (in_array('--c', $param)) {
                $withController = TRUE;
            }
            if (in_array('--soft-delete', $param)) {
                $withSoftDelete = TRUE;
            }
        }

        // Parse input as printable string.
        $print = $this->parseInputModel($name, $withResources, $withSoftDelete);

        $name = $this->parseFileName('M_' . $name);
        // Create model file.
        $this->createFile($this->getPath(), $name);

        // Write to newly created model file.
        fwrite($this->filePointer, $print . PHP_EOL);

        if ($withController) {
            $args = [];
            if ($withResources) {
                $args = ['--r'];
            }
            $this->controller($fullName, $args);
        }

        print('MODEL CREATED: ' . $this->parseGreenText($this->getPath() . $name));
        return;
    }

    /**
     * Parse input as printable string for seeder file.
     *
     * @param string $name
     * @param string $rand    Random string
     * @param array  $results
     *
     * @return string
     */
    private function parseInputSeeder($name, $rand, $results)
    {
        // Array keys for column name.
        $keys = array_keys($results[0]);

        // Reverse array to Descending.
        // We don't know which incremental value this table has and which one should we use, so we do it manually.
        asort($results);

        // Rebase the array.
        $results = array_values($results);

        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class Migration_Seeder_' . $name . '_'.$rand.' extends CI_Migration {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * DB Connection name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param object $' . $this->getConn() . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $' . $this->getConn() . ';' . PHP_EOL . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Table name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $name' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $name;' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        $this->' . $this->getConn() . ' = $this->load->database(\'' . $this->getConn() . '\', TRUE);' . PHP_EOL;
        $print .= '        $this->name = \'' . $name . '\';' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Run migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function up() {' . PHP_EOL;
        $print .= '        $param = [];' . PHP_EOL . PHP_EOL;
        foreach ($results as $key => $res) {
            $print .= '        $param[] = [' . PHP_EOL;
            foreach ($keys as $k) {
                // Replace ALL ' (single quote) with " (double quote) from the value.
                $r = is_null($res[$k]) ? 'NULL' : '\'' . str_replace("'", '"', $res[$k]) . '\'';

                // For DateTime value
                if (in_array($k, $this->getDateTime()) && $r !== 'NULL') {
                    $r = 'date(\'Y-m-d H:i:s.\', strtotime('.$r.')).gettimeofday()[\'usec\']';
                }
                // Trim values
                $r = htmlspecialchars(trim(strip_tags($r)));
                // Delete whitespace and newline
                $r = str_replace(["\t", "\r", "\n", "\\"], ['', '', '', ''], $r);
                $print .= '            \'' . $k . '\' => ' . $r . ',' . PHP_EOL;
            }
            $print .= '        ];' . PHP_EOL; // end $param[]
        }
        $print .= PHP_EOL;
        $print .= '        $chunk = array_chunk($param, 10000);' . PHP_EOL;
        $print .= '        foreach ($chunk as $c) {' . PHP_EOL;
        $print .= '            $this->' . $this->getConn() . '->insert_batch($this->name, $c);' . PHP_EOL;
        $print .= '        }' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Rollback migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function down() {' . PHP_EOL;
        $print .= '        $this->' . $this->getConn() . '->truncate($this->name);' . PHP_EOL;
        $print .= '    }' . PHP_EOL; // end public function down()
        $print .= '}'; // end class

        return $print;
    }

    /**
     * Parse input as printable string for migration file.
     *
     * @param string $name
     * @param string $rand
     * @param string $prefix
     * @param array  $param
     *
     * @return string
     */
    private function parseInputMigration($name, $rand, $prefix, $param)
    {
        $softDelete = '';
        if (in_array('--soft-delete', $param)) {
            $softDelete .= "            'deleted_by' => [" . PHP_EOL;
            $softDelete .= "                'type' => 'VARCHAR'," . PHP_EOL;
            $softDelete .= "                'constraint' => 50," . PHP_EOL;
            $softDelete .= "                'null' => TRUE," . PHP_EOL;
            $softDelete .= '            ],' . PHP_EOL;
            $softDelete .= "            'deleted_at' => [" . PHP_EOL;
            $softDelete .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
            $softDelete .= "                'null' => TRUE," . PHP_EOL;
            $softDelete .= '            ],' . PHP_EOL;
        }
        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class Migration_' . ucwords($prefix) . '_' . $name . '_' . $rand . ' extends CI_Migration {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Array table fields.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param array $fields' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $fields;' . PHP_EOL . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Array table old fields. For the purpose of rolling back a migration.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $oldFields' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $oldFields;' . PHP_EOL . PHP_EOL;
        } else {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Primary key.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $primary' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $primary;' . PHP_EOL . PHP_EOL;
        }
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Table name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $name' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $name;' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        $this->name = \'' . $name . '\';' . PHP_EOL;
        if ($prefix !== 'alter') {
            $print .= '        $this->primary = \'id\';' . PHP_EOL;
        }
        $print .= '        $this->fields = [' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= "            // 'old_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'new_name', // if you want to modify its name" . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= "            // 'old_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'new_name', // if you want to modify its name" . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
        } else {
            $print .= '            $this->primary => [' . PHP_EOL;
            $print .= "                'type' => 'BIGINT'," . PHP_EOL;
            $print .= "                'unsigned' => TRUE," . PHP_EOL;
            $print .= "                'auto_increment' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'created_by' => [" . PHP_EOL;
            $print .= "                'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "                'constraint' => 50," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'created_at' => [" . PHP_EOL;
            $print .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'updated_by' => [" . PHP_EOL;
            $print .= "                'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "                'constraint' => 50," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
            $print .= "            'updated_at' => [" . PHP_EOL;
            $print .= "                'type' => 'TIMESTAMP'," . PHP_EOL;
            $print .= "                'null' => TRUE," . PHP_EOL;
            $print .= '            ],' . PHP_EOL;
        }
        $print .= $softDelete;
        $print .= '        ];' . PHP_EOL; // end public $this->fields
        if ($prefix === 'alter') {
            $print .= '        $this->oldFields = [' . PHP_EOL;
            $print .= $softDelete;
            $print .= "            // 'new_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'old_name'," . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= "            // 'new_name' => [" . PHP_EOL;
            $print .= "            //     'name' => 'old_name'," . PHP_EOL;
            $print .= "            //     'type' => 'VARCHAR'," . PHP_EOL;
            $print .= "            //     'constraint' => 150," . PHP_EOL;
            $print .= "            //     'null' => TRUE," . PHP_EOL;
            $print .= '            // ],' . PHP_EOL;
            $print .= '        ];' . PHP_EOL; // end public $this->oldFields
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function up() {' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '        $this->dbforge->add_column($this->name, $this->fields);' . PHP_EOL;
            $print .= '        // $this->dbforge->modify_column($this->name, $this->fields);' . PHP_EOL;
        } else {
            $print .= '        $this->dbforge->add_field($this->fields);' . PHP_EOL;
            $print .= '        $this->dbforge->add_key($this->primary, TRUE);' . PHP_EOL;
            $print .= '        $this->dbforge->create_table($this->name);' . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function up()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Rollback migration.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return void' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function down() {' . PHP_EOL;
        if ($prefix === 'alter') {
            $print .= '        foreach ($this->fields as $index => $name) {' . PHP_EOL;
            $print .= '            $this->dbforge->drop_column($this->name, $index);' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
            $print .= '        // $this->dbforge->modify_column($this->name, $this->oldFields);' . PHP_EOL;
        } else {
            $print .= '        $this->dbforge->drop_table($this->name, TRUE);' . PHP_EOL;
        }
        $print .= '    }' . PHP_EOL; // end public function down()
        $print .= '}'; // end class

        return $print;
    }

    /**
     * Parse input as printable string for controller file.
     *
     * @param string $name
     * @param bool   $param
     *
     * @return string
     */
    private function parseInputController($name, $param = FALSE)
    {
        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class ' . $name . ' extends CI_Controller {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Page title.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $title' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $title;' . PHP_EOL . PHP_EOL;
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        $this->title = \'' . ucwords($this->parseWhiteSpace($name)) . '\';' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Index page.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return view' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function index() {' . PHP_EOL;
        $print .= '        $data = [' . PHP_EOL;
        $print .= '            \'title\' => $this->title,' . PHP_EOL;
        $print .= '        ];' . PHP_EOL . PHP_EOL; // end $data
        $print .= '        return $this->load->view(\'layout/wrapper\', $data);' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function index()
        if ($param) {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Page for create a new data.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return view' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function create() {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function create()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Function to insert data to database.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return response' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function store() {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function store()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Page for showing detail.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string $id' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return view' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function show($id) {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function show()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Page for edit a data with $id parameter.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string $id' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return view' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function edit($id) {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function edit()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Function to update data in database with $id parameter.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string $id' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return response' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function update($id) {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function update()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Function to delete a data from databse with $id parameter.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string $id' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return response' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function destroy($id) {' . PHP_EOL;
            $print .= '        //' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function destroy()
        }
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Function for datatables.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return string JSON' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function datatables() {' . PHP_EOL;
        $print .= '        $draw = $this->input->post(\'draw\');' . PHP_EOL;
        $print .= '        $length = $this->input->post(\'length\');' . PHP_EOL;
        $print .= '        $start = $this->input->post(\'start\');' . PHP_EOL;
        $print .= '        $search = $this->input->post(\'search\') ? strtolower($this->input->post(\'search\')) : null;' . PHP_EOL;
        $print .= '        // $columnIndex = $this->input->post(\'order\')[0][\'column\']; // Column index' . PHP_EOL;
        $print .= '        // $columnName = $this->input->post(\'columns\')[$columnIndex][\'data\']; // Column name' . PHP_EOL;
        $print .= '        // $columnSortOrder = $this->input->post(\'order\')[0][\'dir\']; // asc or desc' . PHP_EOL . PHP_EOL;
        $print .= '        // Your datatables query here.' . PHP_EOL;
        $print .= '        // $datatables = [];' . PHP_EOL;
        $print .= '        // $totalRecordsWithFilter = 0;' . PHP_EOL;
        $print .= '        // $totalRecords = 0;' . PHP_EOL . PHP_EOL;
        $print .= '        $return = [' . PHP_EOL;
        $print .= '            \'status\' => TRUE,' . PHP_EOL;
        $print .= '            \'message\' => \'Data ditemukan\',' . PHP_EOL;
        $print .= '            \'draw\' => intval($draw),' . PHP_EOL;
        $print .= '            // \'aaData\' => $datatables,' . PHP_EOL;
        $print .= '            // \'iTotalDisplayRecords\' => $totalRecordsWithFilter,' . PHP_EOL;
        $print .= '            // \'iTotalRecords\' => $totalRecords,' . PHP_EOL;
        $print .= '        ];' . PHP_EOL;
        $print .= '        echo json_encode($return);' . PHP_EOL;
        $print .= '        return;' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function datatables()
        $print .= '}'; // end class

        return $print;
    }

    /**
     * Parse input as printable string for model file.
     *
     * @param string $name
     * @param bool   $withResources
     * @param bool   $withSoftDelete
     *
     * @return string
     */
    private function parseInputModel($name, $withResources = FALSE, $withSoftDelete = FALSE)
    {
        $print = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" . PHP_EOL . PHP_EOL;
        $print .= 'Class M_' . $name . ' extends CI_model {' . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Default table name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $table' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $table = \'' . strtolower($name) . '\';' . PHP_EOL . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * DB Connection name.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $conn' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $conn = \'default\';' . PHP_EOL . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * DB Connection.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param object $db' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    private $db;' . PHP_EOL . PHP_EOL;
        $print .= '    /**' . PHP_EOL;
        $print .= '     * OpenBravo DB Connection.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param object $ob' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    // private $ob;' . PHP_EOL . PHP_EOL;
        if ($withSoftDelete) {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * State if Soft Delete is used or not. Parameter should be TRUE or FALSE.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param bool $softDelete' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $softDelete = FALSE;' . PHP_EOL . PHP_EOL;
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Soft Delete field names.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $softDeleteParams' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    private $softDeleteParams = [\'deleted_by\', \'deleted_at\'];' . PHP_EOL . PHP_EOL;
        }
        $print .= '    public function __construct() {' . PHP_EOL;
        $print .= '        parent::__construct();' . PHP_EOL;
        $print .= '        // DB Connection, you can use your own db setting name' . PHP_EOL;
        $print .= '        $this->db = $this->load->database($this->conn, TRUE);' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function __construct()
        // $print .= '    /**' . PHP_EOL;
        // $print .= '     * RAW query.' . PHP_EOL;
        // $print .= '     * ' . PHP_EOL;
        // $print .= '     * @param string $query' . PHP_EOL;
        // $print .= '     * ' . PHP_EOL;
        // $print .= '     * @return array|null' . PHP_EOL;
        // $print .= '     */' . PHP_EOL;
        // $print .= '    public function raw($query = \'\') {' . PHP_EOL;
        // $print .= '        if (empty($query)) {' . PHP_EOL;
        // $print .= '            return null;' . PHP_EOL;
        // $print .= '        }' . PHP_EOL;
        // $print .= '        return $this->db->query($query)->result();' . PHP_EOL;
        // $print .= '    }' . PHP_EOL . PHP_EOL; // end public function raw()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Get all data from database.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function get() {' . PHP_EOL;
        $print .= '        $this->db->select();' . PHP_EOL;
        $print .= '        $this->db->from($this->table);' . PHP_EOL;
        if ($withSoftDelete) {
            $print .= '        $this->softDelete(\'clean\');' . PHP_EOL;
        }
        $print .= '        $result = $this->db->get()->result();' . PHP_EOL;
        $print .= '        return $result ? $result : [];' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function get()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Find data based on $id.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $id' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return object|null' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function find($id) {' . PHP_EOL;
        $print .= '        $this->db->select();' . PHP_EOL;
        $print .= '        $this->db->from($this->table);' . PHP_EOL;
        $print .= '        $this->db->where(\'id\', $id);' . PHP_EOL;
        $print .= '        return $this->db->get()->row();' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function find()
        if ($withResources) {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Insert data to database.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $param' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return object' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function create($param) {' . PHP_EOL;
            $print .= '        $this->db->insert($this->table, $param);' . PHP_EOL;
            $print .= '        return $this->find($this->db->insert_id());' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function create()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Batch insert data to database.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param array $param' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return bool' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function insert($param) {' . PHP_EOL;
            $print .= '        $this->db->insert_batch($this->table, $param);' . PHP_EOL;
            $print .= '        return TRUE;' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function insert()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Update data to database based on $id.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string|int $id' . PHP_EOL;
            $print .= '     * @param array      $param' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return object|bool' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function update($id, $param) {' . PHP_EOL;
            $print .= '        $this->db->where(\'id\', $id);' . PHP_EOL;
            $print .= '        $this->db->update($this->table, $param);' . PHP_EOL;
            $print .= '        $result = (bool) $this->db->affected_rows();' . PHP_EOL;
            $print .= '        if (!$result) {' . PHP_EOL;
            $print .= '            return $result;' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
            $print .= '        return $this->find($id);' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function update()
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Delete data from database based on $id.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string|int $id' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return bool' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function destroy($id) {' . PHP_EOL;
            $print .= '        $this->db->where(\'id\', $id)->delete($this->table);' . PHP_EOL;
            $print .= '        return (bool) $this->db->affected_rows();' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function destroy()
            // $print .= '    /**' . PHP_EOL;
            // $print .= '     * Insert data to database with query binding. For PostgreSQL.' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * @param array  $param' . PHP_EOL;
            // $print .= '     * @param string $returning Returned value, all or any field' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * Example:' . PHP_EOL;
            // $print .= '     * $param = [' . PHP_EOL;
            // $print .= '     *   \'name\' => \'Virdi Gunawan\',' . PHP_EOL;
            // $print .= '     *   \'email\' => \'virdigunawann@gmail.com\',' . PHP_EOL;
            // $print .= '     *   \'visited_at\' => ["TO_DATE(\'".date(\'Y-m-d H:i:s\')."\', \'YYYY-MM-DD\')", TRUE],' . PHP_EOL;
            // $print .= '     *   \'created_at\' => ["NOW()", TRUE],' . PHP_EOL;
            // $print .= '     * ];' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * $this->createWBinding($param, \'all\');' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * Query result:' . PHP_EOL;
            // $print .= '     * INSERT INTO users (name, email, visited_at, created_at)' . PHP_EOL;
            // $print .= '     * VALUES (\'Virdi Gunawan\', \'virdigunawann@gmail.com\', TO_DATE(\'2023-02-08 14:07:56\', \'YYYY-MM-DD\'), NOW())' . PHP_EOL;
            // $print .= '     * RETURNING *;' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * @return object|int' . PHP_EOL;
            // $print .= '     */' . PHP_EOL;
            // $print .= '    public function createWBinding($param, $returning = \'all\') {' . PHP_EOL;
            // $print .= '        $fields = $values = [];' . PHP_EOL;
            // $print .= '        // Parse parameters as string.' . PHP_EOL;
            // $print .= '        foreach ($param as $key => $p) {' . PHP_EOL;
            // $print .= '            $fields[] = $key;' . PHP_EOL;
            // $print .= '            // Check if value is an array.' . PHP_EOL;
            // $print .= '            if (is_array($p)) {' . PHP_EOL;
            // $print .= '                // If it is, explode then check if it should be escaped.' . PHP_EOL;
            // $print .= '                list($val, $escape) = $p;' . PHP_EOL;
            // $print .= '                if ((bool) $escape) {' . PHP_EOL;
            // $print .= '                    $p = $val;' . PHP_EOL;
            // $print .= '                } else {' . PHP_EOL;
            // $print .= '                    $p = "\'$val\'";' . PHP_EOL;
            // $print .= '                }' . PHP_EOL;
            // $print .= '            } else {' . PHP_EOL;
            // $print .= '                $p = "\'$p\'";' . PHP_EOL;
            // $print .= '            }' . PHP_EOL;
            // $print .= '            $values[] = $p;' . PHP_EOL;
            // $print .= '        }' . PHP_EOL . PHP_EOL;
            // $print .= '        // Returned value, either all or specific field.' . PHP_EOL;
            // $print .= '        $return = $returning === \'all\' ? \'*\' : \'id\';' . PHP_EOL;
            // $print .= '        // Implode field names and parameters with create query.' . PHP_EOL;
            // $print .= '        $query = \'INSERT INTO \' . $this->table . \'(\'.join(\', \', $fields).\') VALUES (\'.join(\', \', $values).\') RETURNING \' . $return . \';\';' . PHP_EOL;
            // $print .= '        return $this->db->query($query);' . PHP_EOL;
            // $print .= '    }' . PHP_EOL . PHP_EOL; // end public function createWBinding()
            // $print .= '    /**' . PHP_EOL;
            // $print .= '     * Query binding, for Oracle DB Date format.' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * @param array $where' . PHP_EOL;
            // $print .= '     * @param array $param' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * Example:' . PHP_EOL;
            // $print .= '     * $where = [' . PHP_EOL;
            // $print .= '     *   \'id\' => 1,' . PHP_EOL;
            // $print .= '     * ];' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * $param = [' . PHP_EOL;
            // $print .= '     *   \'name\' => \'Virdi Gunawan\',' . PHP_EOL;
            // $print .= '     *   \'email\' => \'virdigunawann@gmail.com\',' . PHP_EOL;
            // $print .= '     *   \'updated_at\' => ["TO_DATE(\'".date(\'Y-m-d H:i:s\')."\', \'YYYY-MM-DD hh24:mi:ss\')", TRUE],' . PHP_EOL;
            // $print .= '     * ];' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * $this->binding($where, $param);' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * Result:' . PHP_EOL;
            // $print .= '     * UPDATE users' . PHP_EOL;
            // $print .= '     * SET "name" = \'Virdi Gunawan\',' . PHP_EOL;
            // $print .= '     * "email" = \'virdigunawann@gmail.com\',' . PHP_EOL;
            // $print .= '     * "updated_at" = TO_DATE(\'2023-02-08 14:07:56\', \'YYYY-MM-DD hh24:mi:ss\')' . PHP_EOL;
            // $print .= '     * WHERE "id" = \'1\';' . PHP_EOL;
            // $print .= '     * ' . PHP_EOL;
            // $print .= '     * @return string' . PHP_EOL;
            // $print .= '     */' . PHP_EOL;
            // $print .= '    public function binding($where, $param) {' . PHP_EOL;
            // $print .= '        $tempSet = $tempWhere = [];' . PHP_EOL;
            // $print .= '        // Parse parameters as string.' . PHP_EOL;
            // $print .= '        foreach ($param as $keyP => $p) {' . PHP_EOL;
            // $print .= '            // Check if value is an array.' . PHP_EOL;
            // $print .= '            if (is_array($p)) {' . PHP_EOL;
            // $print .= '                // If it is, explode then check if it should be escaped.' . PHP_EOL;
            // $print .= '                list($val, $escape) = $p;' . PHP_EOL;
            // $print .= '                if ((bool) $escape) {' . PHP_EOL;
            // $print .= '                    $p = $val;' . PHP_EOL;
            // $print .= '                } else {' . PHP_EOL;
            // $print .= '                    $p = "\'$val\'";' . PHP_EOL;
            // $print .= '                }' . PHP_EOL;
            // $print .= '            } else {' . PHP_EOL;
            // $print .= '                $p = "\'$p\'";' . PHP_EOL;
            // $print .= '            }' . PHP_EOL;
            // $print .= '            $tempSet[] = "\"$keyP\" = $p";' . PHP_EOL;
            // $print .= '        }' . PHP_EOL . PHP_EOL;
            // $print .= '        // Parse where clauses as string.' . PHP_EOL;
            // $print .= '        foreach ($where as $keyW => $w) {' . PHP_EOL;
            // $print .= '            $tempWhere[] = "\"$keyW\" = \'$w\'";' . PHP_EOL;
            // $print .= '        }' . PHP_EOL . PHP_EOL;
            // $print .= '        $setQuery = join(\', \', $tempSet);' . PHP_EOL;
            // $print .= '        $whereQuery = \' WHERE \' . join(\' AND \', $tempWhere);' . PHP_EOL . PHP_EOL;
            // $print .= '        // Implode where clauses and parameters with update query.' . PHP_EOL;
            // $print .= '        $query = "UPDATE " . $this->table . \' SET \' . $setQuery . $whereQuery . \';\';' . PHP_EOL;
            // $print .= '        return $query;' . PHP_EOL;
            // $print .= '    }' . PHP_EOL . PHP_EOL; // end public function binding()
        }

        $print .= '    /**' . PHP_EOL;
        $print .= '     * Parse string procedures OB.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * Example:' . PHP_EOL;
        $print .= '     * $param = [' . PHP_EOL;
        $print .= "     *   ['string' => 'myname']," . PHP_EOL;
        $print .= "     *   ['int' => '333']," . PHP_EOL;
        $print .= "     *   ['string' => 'mynametwo']," . PHP_EOL;
        $print .= "     *   ['string' => 'myaddress']," . PHP_EOL;
        $print .= "     *   ['string' => '21-Nov-2023']," . PHP_EOL;
        $print .= "     *   ['stringNullable' => null]," . PHP_EOL;
        $print .= "     *   'username'," . PHP_EOL;
        $print .= '     * ];' . PHP_EOL;
        $print .= '     * $procedure = $this->parseProsedur(\'GAI_Pros(null, ?, ?, ?, ?, ?, ?)\', $param);' . PHP_EOL;
        $print .= '     * echo $procedure;' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string $procedure' . PHP_EOL;
        $print .= '     * @param array  $param' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @throws \Exception' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return string|null' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    // public function parseProcedures($procedure, $param) {' . PHP_EOL;
        $print .= '    //     if (substr_count($procedure, \'?\') !== count($param)) {' . PHP_EOL;
        $print .= '    //         throw new Exception(\'Total $param is not equal to placeholder in $procedure!\', 1);' . PHP_EOL;
        $print .= '    //     }' . PHP_EOL . PHP_EOL;
        $print .= "    //     // Replace ? in procedure string to %s because we're using vsprintf" . PHP_EOL;
        $print .= '    //     $procedure = str_replace(\'?\', \'%s\', $procedure);' . PHP_EOL . PHP_EOL;
        $print .= '    //     $temp = [];' . PHP_EOL;
        $print .= '    //     if (count($param) === 0) {' . PHP_EOL;
        $print .= '    //         return null;' . PHP_EOL;
        $print .= '    //     }' . PHP_EOL . PHP_EOL;
        $print .= '    //     foreach ($param as $p) {' . PHP_EOL;
        $print .= '    //         // If $p is not an array, make it one' . PHP_EOL;
        $print .= '    //         if (!is_array($p)) {' . PHP_EOL;
        $print .= '    //             $p = [\'string\' => $p];' . PHP_EOL;
        $print .= '    //         }' . PHP_EOL . PHP_EOL;
        $print .= '    //         // Get its type data' . PHP_EOL;
        $print .= '    //         $var = array_key_first($p);' . PHP_EOL . PHP_EOL;
        $print .= '    //         switch ($var) {' . PHP_EOL;
        $print .= '    //             case \'string\':' . PHP_EOL;
        $print .= '    //                 $temp[] = "\'".$p[$var]."\'";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'int\':' . PHP_EOL;
        $print .= '    //                 $temp[] = $p[$var];' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'stringNullable\':' . PHP_EOL;
        $print .= '    //                 $temp[] = empty($p[$var]) ? \'null\' : "\'".$p[$var]."\'";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'intNullable\':' . PHP_EOL;
        $print .= '    //                 $temp[] = empty($p[$var]) ? \'null\' : $p[$var];' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             default:' . PHP_EOL;
        $print .= '    //                 $temp[] = "\'".$p[$var]."\'";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //         }' . PHP_EOL;
        $print .= '    //     }' . PHP_EOL . PHP_EOL;
        $print .= '    //     return vsprintf($procedure, $temp);' . PHP_EOL;
        $print .= '    // }' . PHP_EOL . PHP_EOL; // end public function parseProcedures()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Run procedures OB.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param array $procedures' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return bool' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    // public function runProcedure($procedures) {' . PHP_EOL;
        $print .= '    //     if (count($procedures) === 0) {' . PHP_EOL;
        $print .= '    //         return true;' . PHP_EOL;
        $print .= '    //     }' . PHP_EOL . PHP_EOL;
        $print .= "    //     // We are going to run every procedures provided." . PHP_EOL;
        $print .= '    //     $this->ob = $this->load->database(\'ob\', TRUE);' . PHP_EOL;
        $print .= '    //     $result = $this->ob->query(\'BEGIN \' . join(\';\', $procedures) . \'; END;\');' . PHP_EOL;
        $print .= '    //     return $result ? true : false;' . PHP_EOL;
        $print .= '    // }' . PHP_EOL . PHP_EOL; // end public function runProcedure()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Datatables.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string|int  $length' . PHP_EOL;
        $print .= '     * @param string|int  $start' . PHP_EOL;
        $print .= '     * @param string|null $search' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function datatables($length = 10, $start = 0, $search = NULL) {' . PHP_EOL;
        $print .= '        $result = $this->queryDatatables($length, $start, $search);' . PHP_EOL;
        $print .= '        $countResult = count($result);' . PHP_EOL . PHP_EOL;
        $print .= '        if ($countResult >= $length) {' . PHP_EOL;
        $print .= '            $resultNextPage = $this->queryDatatables($length, $start + $length, $search);' . PHP_EOL;
        $print .= '            $countResultNextPage = count($resultNextPage);' . PHP_EOL;
        $print .= '            if ($countResultNextPage >= $length) {' . PHP_EOL;
        $print .= '                $totalRecords = $start + (2 * $length);' . PHP_EOL;
        $print .= '            } else {' . PHP_EOL;
        $print .= '                $totalRecords = $start + $length + $countResultNextPage;' . PHP_EOL;
        $print .= '            }' . PHP_EOL;
        $print .= '        } else {' . PHP_EOL;
        $print .= '            $totalRecords = $start + $countResult;' . PHP_EOL;
        $print .= '        }' . PHP_EOL . PHP_EOL;
        $print .= '        return [' . PHP_EOL;
        $print .= '            \'totalRecords\' => $totalRecords,' . PHP_EOL;
        $print .= '            \'data\' => $result ? $result : [],' . PHP_EOL;
        $print .= '        ];' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function datatables()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Datatables.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param string|int  $length' . PHP_EOL;
        $print .= '     * @param string|int  $start' . PHP_EOL;
        $print .= '     * @param string|null $search' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function queryDatatables($length = 10, $start = 0, $search = NULL) {' . PHP_EOL;
        $print .= '        $this->db->select();' . PHP_EOL;
        $print .= '        $this->db->from($this->table);' . PHP_EOL;
        if ($withSoftDelete) {
            $print .= '        $this->softDelete(\'clean\');' . PHP_EOL;
        }
        $print .= '        if (!empty($search)) {' . PHP_EOL;
        $print .= '            // Your LIKE query.' . PHP_EOL;
        $print .= '            // $search = strtolower($search);' . PHP_EOL;
        $print .= '            // $this->db->group_start();' . PHP_EOL;
        $print .= '            //     $this->db->like(\'LOWER(name)\', $search);' . PHP_EOL;
        $print .= '            //     $this->db->or_like(\'LOWER(phone)\', $search);' . PHP_EOL;
        $print .= '            // $this->db->group_end();' . PHP_EOL;
        $print .= '        }' . PHP_EOL;
        $print .= '        $this->db->limit($length, $start);' . PHP_EOL;
        $print .= '        return $this->db->get()->result();' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function queryDatatables()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Total all records with filter.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return int' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function totalRecords() {' . PHP_EOL;
        $print .= '        $this->db->select();' . PHP_EOL;
        $print .= '        $this->db->from($this->table);' . PHP_EOL;
        if ($withSoftDelete) {
            $print .= '        $this->softDelete(\'clean\');' . PHP_EOL;
        }
        $print .= '        $result = $this->db->count_all_results();' . PHP_EOL;
        $print .= '        return $result ? $result : 0;' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function totalRecords()

        if ($withSoftDelete) {
            $print .= '    /**' . PHP_EOL;
            $print .= '     * Soft Delete parameters.' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @param string $switchParam' . PHP_EOL;
            $print .= '     * ' . PHP_EOL;
            $print .= '     * @return void' . PHP_EOL;
            $print .= '     */' . PHP_EOL;
            $print .= '    public function softDelete($switchParam = \'clean\') {' . PHP_EOL;
            $print .= '        if ($this->softDelete) {' . PHP_EOL;
            $print .= '            // - \'clean\' only return all record that IS NOT deleted.' . PHP_EOL;
            $print .= '            // - \'trashed\' only return all record that IS deleted.' . PHP_EOL;
            $print .= '            // - \'all\' return all record.' . PHP_EOL;
            $print .= '            // Default is \'clean\'.' . PHP_EOL;
            $print .= '            switch ($switchParam) {' . PHP_EOL;
            $print .= '                case \'clean\':' . PHP_EOL;
            $print .= '                    $this->db->group_start();' . PHP_EOL;
            $print .= '                        foreach ($this->softDeleteParams as $param) {' . PHP_EOL;
            $print .= '                            $this->db->where("$param IS NULL");' . PHP_EOL;
            $print .= '                        }' . PHP_EOL;
            $print .= '                    $this->db->group_end();' . PHP_EOL;
            $print .= '                    break;' . PHP_EOL;
            $print .= '                case \'trashed\':' . PHP_EOL;
            $print .= '                    $this->db->group_start();' . PHP_EOL;
            $print .= '                        foreach ($this->softDeleteParams as $param) {' . PHP_EOL;
            $print .= '                            $this->db->where("$param IS NOT NULL");' . PHP_EOL;
            $print .= '                        }' . PHP_EOL;
            $print .= '                    $this->db->group_end();' . PHP_EOL;
            $print .= '                    break;' . PHP_EOL;
            $print .= '                case \'all\':' . PHP_EOL;
            $print .= '                    break;' . PHP_EOL;
            $print .= '                default:' . PHP_EOL;
            $print .= '                    $this->db->group_start();' . PHP_EOL;
            $print .= '                        foreach ($this->softDeleteParams as $param) {' . PHP_EOL;
            $print .= '                            $this->db->where("$param IS NULL");' . PHP_EOL;
            $print .= '                        }' . PHP_EOL;
            $print .= '                    $this->db->group_end();' . PHP_EOL;
            $print .= '                    break;' . PHP_EOL;
            $print .= '            }' . PHP_EOL;
            $print .= '        }' . PHP_EOL;
            $print .= '    }' . PHP_EOL . PHP_EOL; // end public function softDelete()
        }
        $print .= '}'; // end class

        return $print;
    }

    /**
     * Create seeder file. Drop if already exists, then create a new one.
     *
     * @param string $path
     * @param string $name
     *
     * @return void
     */
    private function createFile($path, $name)
    {
        $this->folderPermission($path, 0755, 'apache');

        $fullPath = $path . $name;

        $old = umask(0);

        $file = $fullPath;
        // If file exists, drop it.
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $file = fopen($fullPath, 'a') or exit("Can't open $fullPath!");
        umask($old);

        $this->filePointer = $file;
    }

    /**
     * Create folder with 0755 (rwxr-xr-x) permission if doesn't exist.
     * If exists, change its permission to 0755 (rwxrwxrwx).
     * Owner default to www-data:www-data.
     *
     * @param string $path
     * @param string $mode
     * @param string $owner
     *
     * @return void
     */
    private function folderPermission($path, $mode = 0755, $owner = 'www-data:www-data')
    {
        if (!is_dir($path)) {
            // If folder doesn't exist, create a new one with permission (rwxrwxrwx).
			$old = umask(0);
            mkdir($path, $mode, TRUE);
            @chown($path, $owner);
            // @chgrp($path, $owner);
			umask($old);
        } else {
            // If exists, change its permission to 0755 (rwxr-xr-x).
			$old = umask(0);
            @chmod($path, $mode);
            @chown($path, $owner);
            // @chgrp($path, $owner);
			umask($old);
        }
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     * Stolen from laravel helper.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    private function afterLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === FALSE) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Parse the given arguments to determine if they are name string or arguments.
     *
     * @param array $args
     *
     * @return object
     */
    public function parseParam($args)
    {
        if (!$args) {
            return (object) [
                'command' => '',
                'name' => '',
                'args' => [],
            ];
        }

        $command = '';
		$name = $param = [];
		foreach ($args as $key => $arg) {
			if ($this->startsWith($arg, '--')) {
				$param[] = $arg;
			} elseif ($this->startsWith($arg, 'create:')) {
				$command = $this->afterLast($arg, ':');
			} else {
				$name[] = $arg;
			}
		}

        return (object) [
            'command' => $command,
            'name' => join(DIRECTORY_SEPARATOR, $name), // Implode/Join array name with DIRECTORY_SEPARATOR.
            'args' => array_values(array_unique($param)), // Distinct, then rebase the arguments array.
        ];
    }

    /**
     * Determine if a given string starts with a given substring. Case sensitive.
     * Stolen from laravel helper.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    private function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     * Stolen from laravel helper.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    private function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, TRUE);

        return $result === FALSE ? $subject : $result;
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     * Stolen from laravel helper.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    private function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === FALSE) {
            return $subject;
        }

        return substr($subject, 0, $pos);
    }

    /**
     * Get latest migration order.
     * Default is sequential, if there is no migration file exist.
     *
     * @param string $path
     *
     * @return string
     */
    private function latest($path)
    {
        if ($this->getMigrationType() === 'timestamp') {
            return date('YmdHis');
        }

        // Get all migration files.
        $seeders = $path . '*.php';
        $globs = array_filter(glob($seeders), 'is_file');
        if (count($globs) > 0) {
            // Reverse the array.
            rsort($globs);

            // Get the latest array order.
            $latestMigration = (int) $this->before($this->afterLast($globs[0], '\\'), '_');
            $count = str_pad($latestMigration + 1, $this->countLatest($latestMigration), '0', STR_PAD_LEFT);
        } else {
            // Default is sequential order, not timestamp.
            $count = '001';
        }

        return $count;
    }

    /**
     * Count latest migration. Return 3 digit number by default.
     * 
     * @param int $latest
     * 
     * @return int
     */
    private function countLatest($latest) {
        // To verify if the next number digit is increased or not.
        // Ex. strlen(800 + 1) = 3
        $nextNumber = strlen($latest + 1);
        // Count the next digit.
        // Ex. strlen(800) + 1 = 4
        $nextDigit = strlen($latest) + 1;
        $result = $nextNumber === $nextDigit ? $nextDigit : $nextNumber;
        return $result < 3 ? 3 : $result;
    }

    /**
     * Parse file name with .php extension.
     *
     * @param string $name
     *
     * @return string
     */
    private function parseFileName($name)
    {
        return $name . '.php';
    }

    /**
     * Random string.
     *
     * @param string|int $length
     *
     * @return string
     */
    private function rand($length = 4)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, ceil($length / strlen($pool)))), 0, $length);
    }

    /**
     * Parse returned text with green color.
     *
     * @param string $text
     *
     * @return string
     */
    private function parseGreenText($text)
    {
        return "\033[92m" . $this->emoticon($text) . "\033[0m" . "\n";
    }

    /**
     * Parse returned text with red color.
     *
     * @param string $text
     *
     * @return string
     */
    private function parseRedText($text)
    {
        return "\e[31m" . $text . "\033[0m" . "\n";
    }

    /**
     * Parse returned text with emoticon for fun h3h3.
     *
     * @param string $text
     *
     * @return string
     */
    public function emoticon($text)
    {
        return $text . ' ' . $this->OwO[array_rand($this->OwO, 1)];
    }

    /**
     * Parse special character to whitespace.
     *
     * @param string $text
     *
     * @return string
     */
    private function parseWhiteSpace($text)
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", ' ', $text);
    }

    /**
     * Add date time fields to current date time's array.
     *
     * @param array $fields
     *
     * @return void
     */
    public function addDateTime($fields = [])
    {
        $old = $this->dateTime;
        $this->dateTime = array_values(array_unique(array_merge($old, $fields)));
    }

    /**
     * Get array date time.
     *
     * @return array
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set path to seeder folder.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath($path = SEEDER_PATH)
    {
        // Path shouldn't have trailing slash or backslash.
        // We'are going to add DIRECTORY_SEPARATOR after the path ourself.
        $path = rtrim($path, '\\/');
        $this->path = $path . DIRECTORY_SEPARATOR;
    }

    /**
     * Set which connection used for this seeder.
     *
     * @param string $conn
     *
     * @return void
     */
    public function setConn($conn = 'default')
    {
        $this->conn = $conn;
    }

    /**
     * Set which migration type you're using fot this seeder.
     *
     * @param string $type
     *
     * @return void
     */
    public function setMigrationType($type = 'sequential')
    {
        $this->migrationType = $type;
    }

    /**
     * Get path to seeder folder. Default to constant SEEDER_PATH.
     *
     * @return string
     */
    private function getPath()
    {
        return $this->path ?: SEEDER_PATH . DIRECTORY_SEPARATOR;
    }

    /**
     * Get which connection used for seeder. Default is 'default'.
     *
     * @return string
     */
    private function getConn()
    {
        return $this->conn ?: 'default';
    }

    /**
     * Get which migration type you're using.
     *
     * @return string
     */
    private function getMigrationType()
    {
        return $this->migrationType;
    }
}