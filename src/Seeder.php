<?php 

namespace Virdiggg\SeederCi3;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;
use Virdiggg\SeederCi3\Helpers\FileHelper as Fl;
use Virdiggg\SeederCi3\Templates\ControllerTemplate as Cont;
use Virdiggg\SeederCi3\Templates\SeederTemplate as Se;
use Virdiggg\SeederCi3\Templates\MigrationTemplate as Mig;
use Virdiggg\SeederCi3\Templates\ModelTemplate as Mod;

defined('APPPATH') or define('APPPATH', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR);

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
     * Migration type. sequential or timestamp
     *
     * @param string
     */
    private $migrationType;

    /**
     * Helpers
     *
     * @param object
     */
    private $str; // String
    private $fl; // File

    /**
     * Printer templates
     * 
     * @param object
     */
    private $cont;
    private $se;
    private $mig;
    private $mod;

    const APP_PATH = APPPATH;
    const SEEDER_PATH = self::APP_PATH.'migrations';

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->str = new Str();
        $this->fl = new Fl();
    }

    /**
     * Help options
     * 
     * @return void
     */
    public function help() {
        $arr = [
            [
                'label' => 'To open help options',
                'cmd' => 'php index.php app help',
            ],
            [
                'label' => 'To run migration',
                'cmd' => 'php index.php app migrate',
            ],
            [
                'label' => 'To rollback migration',
                'cmd' => 'php index.php app rollback [--to=number]',
            ],
            [
                'label' => 'To create seeder file based on table',
                'cmd' => 'php index.php app seed [table_name]',
            ],
            [
                'label' => 'To create migration file based on table',
                'cmd' => 'php index.php app migration [table_name] [--soft-delete] ',
            ],
            [
                'label' => 'To create model file',
                'cmd' => 'php index.php app model [dir/model_name] [--r] [--c] [--soft-delete]',
            ],
            [
                'label' => 'To create controller file',
                'cmd' => 'php index.php app controller [dir/controller_name] [--r]',
            ],
        ];

        foreach ($arr as $a) {
            $msg = $this->str->yellowText($a['label']) . $this->str->greenText($a['cmd'], false) . "\n";
            print($msg);
        }
        return;
    }

    /**
     * Query seeder
     * 
     * @param string   $tableName
     * @param int|null $limit
     * 
     * @return array
     */
    private function querySeeder($tableName, $limit = null) {
        $this->db->select();
        $this->db->from(trim($tableName));
        if (!empty($limit)) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result_array();
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
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // This is a different connection.
        // So don't be confused with the one we are going to print in seeder file.
        $this->db = $this->CI->load->database($this->getConn(), TRUE);

        if (!$this->db->table_exists($name)) {
            print($this->str->redText('TABLE "' . $name . '" NOT FOUND IN YOUR DATABASE ╰(*°▽°*)╯'));
            return;
        }

        $limit = null;
        foreach ($param as $arg) {
            if (strpos($arg, '--limit=') !== false) {
                $limit = substr($arg, strpos($arg, '--to=') + 8);
            }
        }

        $results = $this->querySeeder($name, $limit);

        if (count($results) === 0) {
            print($this->str->redText('NO RECORDS IN TABLE "' . $name . '" ╰(*°▽°*)╯'));
            return;
        }

        $rand = $this->str->rand(4);

        $this->se = new Se($this->getConn(), $this->dateTime);
        // Parse input as printable string.
        $print = $this->se->template($name, $rand, $results);

        // Get the latest migration file order.
        $count = $this->latest($this->getPath());

        $name = $this->str->parseFileName($count . '_seeder_' . $name . '_' . $rand);
        // Create seeder file.
        $this->fl->printFile($this->getPath(), $name, $print);

        print('SEEDER CREATED: ' . $this->str->greenText($this->getPath() . $name));
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
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // This is a different connection.
        // So don't be confused with the one we are going to print in migration file.
        $this->db = $this->CI->load->database($this->getConn(), TRUE);

        $prefix = 'create';
        if ($this->db->table_exists($name)) {
            $prefix = 'alter';
        }

        $rand = $this->str->rand(4);

        $this->mig = new Mig($this->getConn(), $this->dateTime);
        // Parse input as printable string.
        $print = $this->mig->template($name, $rand, $prefix, $param);

        // Get the latest migration file order.
        $count = $this->latest($this->getPath());

        $name = $this->str->parseFileName($count . '_' . $prefix . '_' . $name . '_' . $rand);
        // Create migration file.
        $this->fl->printFile($this->getPath(), $name, $print);

        print('MIGRATION CREATED: ' . $this->str->greenText($this->getPath() . $name));
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
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->str->beforeLast($fullName, '\\');
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to controllers folder
        $this->setPath(self::APP_PATH . 'controllers' . $before);

        // File name is after the last slash \.
        $name = $this->str->afterLast($fullName, '\\');

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        // Parse input as printable string.
        $this->cont = new Cont();
        $print = $this->cont->template($name, $param);

        $name = $this->str->parseFileName($name);
        // Create controller file.
        $this->fl->printFile($this->getPath(), $name, $print);

        print('CONTROLLER CREATED: ' . $this->str->greenText($this->getPath() . $name));
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
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->str->beforeLast($fullName, '\\');
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to models folder
        $this->setPath(self::APP_PATH . 'models' . $before);

        // File name is after the last slash \.
        $name = $this->str->afterLast($fullName, '\\');

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        // Parse input as printable string.
        $this->mod = new Mod();
        $print = $this->mod->template($name, $param);

        $name = $this->str->parseFileName('M_' . $name);
        // Create model file.
        $this->fl->printFile($this->getPath(), $name, $print);

        if (in_array('--r', $param)) {
            $withResources = TRUE;
        }
        if (in_array('--c', $param)) {
            $withController = TRUE;
        }

        print('MODEL CREATED: ' . $this->str->greenText($this->getPath() . $name));

        if ($withController) {
            $args = [];
            if ($withResources) {
                $args = ['--r'];
            }
            $this->controller($fullName, $args);
        }

        return;
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
            if ($this->str->startsWith($arg, '--')) {
                $param[] = $arg;
            } elseif ($this->str->startsWith($arg, 'create:')) {
                $command = $this->str->afterLast($arg, ':');
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
     * Get latest migration order.
     * Default is sequential, if there is no migration file exist.
     *
     * @param string $path
     *
     * @return string
     */
    private function latest($path)
    {
        if ($this->migrationType === 'timestamp') {
            return date('YmdHis');
        }

        // Get all migration files.
        $seeders = $path . '*.php';
        $globs = array_filter(glob($seeders), 'is_file');
        if (count($globs) > 0) {
            // Reverse the array.
            rsort($globs);

            // Get the latest array order.
            $latestMigration = (int) $this->str->before($this->str->afterLast($globs[0], '\\'), '_');
            if ($latestMigration > 990) {
                print($this->str->redText('WARNING: CODEIGNITER 3 MIGRATION CANNOT HANDLE MIGRATION NUMBER 1000, PLEASE SWITCH TO TIMESTAMP ╰(*°▽°*)╯'));
            } 
            $count = str_pad($latestMigration + 1, $this->str->countLatest($latestMigration), '0', STR_PAD_LEFT);
        } else {
            // Default is sequential order, not timestamp.
            $count = '001';
        }

        return $count;
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
     * Set path to seeder folder.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath($path = self::SEEDER_PATH)
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
        return $this->path ?: self::SEEDER_PATH . DIRECTORY_SEPARATOR;
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
}