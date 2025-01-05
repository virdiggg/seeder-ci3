<?php

namespace Virdiggg\SeederCi3\Templates;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class ControllerTemplate
{
    private $str;
    public function __construct()
    {
        $this->str = new Str();
    }

    /**
     * Parse input as printable string for controller file.
     *
     * @param string $name
     * @param array  $param
     * @param array  $constructors List of additional function to be called in constructor.
     *
     * @return string
     */
    public function template($name, $param = [], $constructors = [])
    {
        $withResources = FALSE;
        if (count($param) > 0) {
            if (in_array('--r', $param)) {
                $withResources = TRUE;
            }
        }

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
        $print .= '        $this->title = \'' . $this->str->parseTitle($name) . '\';' . PHP_EOL;
        foreach ($constructors as $constructor) {
            $print .= '        ' . $constructor . PHP_EOL;
        }
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
        if ($withResources) {
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
        $print .= '        $draw = $this->input->post(\'draw\') ?: 1;' . PHP_EOL;
        $print .= '        $length = $this->input->post(\'length\') ?: 10;' . PHP_EOL;
        $print .= '        $start = $this->input->post(\'start\') ?: 0;' . PHP_EOL;
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
}