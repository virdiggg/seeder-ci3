<?php

namespace Virdiggg\SeederCi3;

use Virdiggg\SeederCi3\Helpers\StrHelper as Str;
use Virdiggg\SeederCi3\Helpers\FileHelper as Fl;
use Virdiggg\SeederCi3\Helpers\EnvHelper as Ev;
use Virdiggg\SeederCi3\Templates\ControllerTemplate as Cont;
use Virdiggg\SeederCi3\Templates\SeederTemplate as Se;
use Virdiggg\SeederCi3\Templates\MigrationTemplate as Mig;
use Virdiggg\SeederCi3\Templates\ModelTemplate as Mod;
use Virdiggg\SeederCi3\Templates\HelpTemplate as Help;

defined('APPPATH') or define('APPPATH', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
defined('SEEDER_CONFIG_PATH') or define('SEEDER_CONFIG_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'seeder.php');
defined('CI3_CONFIG_PATH') or define('CI3_CONFIG_PATH', APPPATH . 'config' . DIRECTORY_SEPARATOR);

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
     * Custom DB driver setting.
     *
     * @param string
     */
    public $driver;

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
    private $env; // Environment

    /**
     * Printer templates
     * 
     * @param object
     */
    private $cont;
    private $se;
    private $mig;
    private $mod;
    private $help;

    const APP_PATH = APPPATH;
    const SEEDER_PATH = self::APP_PATH . 'migrations';

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->str = new Str();
        $this->fl = new Fl();
        $this->env = new Ev();
    }

    /**
     * Copy seeder.php config files to application/config
     * 
     * @return void
     */
    public function copyConfig()
    {
        $this->fl->copyConfig();
    }

    /**
     * Create a simple seeder file.
     *
     * @param string $fullName     Table name
     * @param array  $param        Optional parameter
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return void
     */
    public function seed($name = '', $param = [], $constructors = [])
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

        $this->se = new Se($this->getConn(), $this->dateTime, $this->driver);
        // Parse input as printable string.
        $print = $this->se->template($name, $rand, $results, $constructors);

        // Get the latest migration file order.
        $count = $this->str->latest($this->migrationType, $this->getPath());

        $name = $this->str->parseFileName($count . '_seeder_' . $name . '_' . $rand);
        // Create seeder file.
        $result = $this->fl->printFile($this->getPath(), $name, $print);
        if (!$result) {
            return;
        }

        $this->fl->modifyConfig($count);

        print('SEEDER CREATED: ' . $this->str->greenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple migration file.
     *
     * @param string $fullName     Table name
     * @param array  $param        Optional parameter
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return void
     */
    public function migration($name = '', $param = [], $constructors = [])
    {
        if (!$name) {
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // This is a different connection.
        // So don't be confused with the one we are going to print in migration file.
        $this->db = $this->CI->load->database($this->getConn(), TRUE);

        // Set path to migrations folder
        $this->setPath(self::APP_PATH . 'migrations');

        $prefix = 'create';
        if ($this->db->table_exists($name)) {
            $prefix = 'alter';
        }

        $rand = $this->str->rand(4);

        $this->mig = new Mig($this->driver);
        // Parse input as printable string.
        $print = $this->mig->template($name, $rand, $prefix, $param, $constructors);

        // Get the latest migration file order.
        $count = $this->str->latest($this->migrationType, $this->getPath());

        $name = $this->str->parseFileName($count . '_' . $prefix . '_' . $name . '_' . $rand);
        // Create migration file.
        $result = $this->fl->printFile($this->getPath(), $name, $print);
        if (!$result) {
            return;
        }

        $this->fl->modifyConfig($count);

        print('MIGRATION CREATED: ' . $this->str->greenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple controller file.
     *
     * @param string $fullName     Table name
     * @param array  $param        Optional parameter
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return void
     */
    public function controller($fullName = '', $param = [], $constructors = [])
    {
        if (!$fullName) {
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // Normalize slash.
        $fullName = $this->str->normalizeSlash($fullName);

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->str->beforeLast($fullName, DIRECTORY_SEPARATOR);
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to controllers folder
        $this->setPath(self::APP_PATH . 'controllers' . $before);

        // File name is after the last slash \.
        $name = $this->str->afterLast($fullName, DIRECTORY_SEPARATOR);

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        // Parse input as printable string.
        $this->cont = new Cont();
        $print = $this->cont->template($name, $param, $constructors);

        $name = $this->str->parseFileName($name);
        // Create controller file.
        $result = $this->fl->printFile($this->getPath(), $name, $print);
        if (!$result) {
            return;
        }

        print('CONTROLLER CREATED: ' . $this->str->greenText($this->getPath() . $name));
        return;
    }

    /**
     * Create a simple model file.
     *
     * @param string $fullName     Table name
     * @param array  $param        Optional parameter
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return void
     */
    public function model($fullName = '', $param = [], $constructors = [])
    {
        if (!$fullName) {
            print($this->str->redText('PARAMETER NOT FOUND ╰(*°▽°*)╯'));
            return;
        }

        // Normalize slash.
        $fullName = $this->str->normalizeSlash($fullName);

        // File path is before the last slash \. If exists, add another slash.
        $before = $this->str->beforeLast($fullName, DIRECTORY_SEPARATOR);
        if ($before) {
            $before = DIRECTORY_SEPARATOR . $before;
        }

        // Set path to models folder
        $this->setPath(self::APP_PATH . 'models' . $before);

        // File name is after the last slash \.
        $name = $this->str->afterLast($fullName, DIRECTORY_SEPARATOR);

        // Ucfirst for file and class name
        $name = ucfirst(strtolower(trim($name)));

        // Parse input as printable string.
        $this->mod = new Mod($this->driver);
        $print = $this->mod->template($name, $param, $constructors);

        $name = $this->str->parseFileName('M_' . $name);
        // Create model file.
        $result = $this->fl->printFile($this->getPath(), $name, $print);

        if (in_array('--c', $param)) {
            $this->controller($fullName, $param);
        }
        if (in_array('--m', $param)) {
            $this->migration($fullName, $param);
        }

        if (!$result) {
            return;
        }

        print('MODEL CREATED: ' . $this->str->greenText($this->getPath() . $name));
        return;
    }

    /**
     * Help options
     * 
     * @return void
     */
    public function help()
    {
        $this->help = new Help();
        $this->help->template();
    }

    /**
     * Query seeder
     * 
     * @param string   $tableName
     * @param int|null $limit
     * 
     * @return array
     */
    private function querySeeder($tableName, $limit = null)
    {
        $this->db->select();
        $this->db->from(trim($tableName));
        if (!empty($limit)) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result_array();
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
     * Add date time fields to current date time's array.
     *
     * @param array $fields
     *
     * @return void
     */
    public function addDateTime($fields = [])
    {
        $old = $this->dateTime;
        $this->dateTime = array_values(array_unique(array_merge($old, (array) $fields)));
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
        $this->path = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
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
        $this->setDriver($conn);
    }

    /**
     * Set which connection used for this seeder.
     *
     * @param string $conn
     *
     * @return void
     */
    private function setDriver($conn = 'default')
    {
        $this->driver = $this->env->verifyDBDir($conn);
    }

    /**
     * Set which migration type you're using fot this seeder.
     *
     * @param string $type
     *
     * @return void
     */
    public function setMigrationType($type = 'timestamp')
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
