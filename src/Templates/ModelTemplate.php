<?php

namespace Virdiggg\SeederCi3\Templates;
use Virdiggg\SeederCi3\Helpers\EnvHelper as Ev;

class ModelTemplate
{
    private $env; // Environment

    public function __construct()
    {
        $this->env = new Ev();
    }

    /**
     * Parse input as printable string for migration file.
     *
     * @param string $name
     * @param array  $param
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return string
     */
    public function template($name, $param = [], $constructors = [])
    {
        $withResources = $withSoftDelete = FALSE;
        if (count($param) > 0) {
            if (in_array('--r', $param)) {
                $withResources = TRUE;
            }
            if (in_array('--soft-delete', $param)) {
                $withSoftDelete = TRUE;
            }
        }

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
        $print .= '     * Another DB Connection.' . PHP_EOL;
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
        $print .= '        // $this->ob = $this->load->database(\'otherdb\', TRUE);' . PHP_EOL;
        foreach ($constructors as $constructor) {
            $print .= '        ' . $constructor . PHP_EOL;
        }
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
        if ($this->env->belowPHP5()) {
            $print .= '    //         $var = array_keys($p)[0];' . PHP_EOL . PHP_EOL;
        } else {
            $print .= '    //         $var = array_key_first($p);' . PHP_EOL . PHP_EOL;
        }
        $print .= '    //         switch ($var) {' . PHP_EOL;
        $print .= '    //             case \'string\':' . PHP_EOL;
        $print .= '    //                 $temp[] = "\'".str_replace("\'", "\'\'", $p[$var])."\'";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'int\':' . PHP_EOL;
        $print .= '    //                 $temp[] = $p[$var];' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'date\':' . PHP_EOL;
        $print .= '    //                 $temp[] = "TO_DATE(\'".$p[$var]."\', \'YYYY-MM-DD\')";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'stringNullable\':' . PHP_EOL;
        $print .= '    //                 $temp[] = empty($p[$var]) ? \'null\' : "\'".str_replace("\'", "\'\'", $p[$var])."\'";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'intNullable\':' . PHP_EOL;
        $print .= '    //                 $temp[] = empty($p[$var]) ? \'null\' : $p[$var];' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             case \'dateNullable\':' . PHP_EOL;
        $print .= '    //                 $temp[] = empty($p[$var]) ? \'null\' : "TO_DATE(\'".$p[$var]."\', \'YYYY-MM-DD\')";' . PHP_EOL;
        $print .= '    //                 break;' . PHP_EOL;
        $print .= '    //             default:' . PHP_EOL;
        $print .= '    //                 $temp[] = "\'".str_replace("\'", "\'\'", $p[$var])."\'";' . PHP_EOL;
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
        $print .= '            \'data\' => $result ? $this->parse($result, $start) : [],' . PHP_EOL;
        $print .= '        ];' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function datatables()
        $print .= '    /**' . PHP_EOL;
        $print .= '     * Parse datatables.' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @param array      $length' . PHP_EOL;
        $print .= '     * @param string|int $start' . PHP_EOL;
        $print .= '     * ' . PHP_EOL;
        $print .= '     * @return array' . PHP_EOL;
        $print .= '     */' . PHP_EOL;
        $print .= '    public function parse($result, $start = 0) {' . PHP_EOL;
        $print .= '        foreach ($result as $r) {' . PHP_EOL;
        $print .= '            $start++;' . PHP_EOL;
        $print .= '            $r->no = $start;' . PHP_EOL;
        $print .= '            $r->action = \'\';' . PHP_EOL;
        $print .= '        }' . PHP_EOL . PHP_EOL;
        $print .= '        return $result;' . PHP_EOL;
        $print .= '    }' . PHP_EOL . PHP_EOL; // end public function parse()
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
}