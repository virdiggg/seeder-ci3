<?php

namespace Virdiggg\SeederCi3\Templates;

use Virdiggg\SeederCi3\Utils\Str;

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
      if (isset($param['r']) && $param['r']) {
        $withResources = TRUE;
      }
    }

    $resourceMethods = '';

    if ($withResources) {
        $resourceMethods = <<<'PHP'

        /**
         * Page for creating a new data.
         *
         * @return view
         */
        public function create()
        {
            //
        }

        /**
         * Function to insert data into database.
         *
         * @return response
         */
        public function store()
        {
            //
        }

        /**
         * Page for showing detail.
         *
         * @param string $id
         * @return view
         */
        public function show($id)
        {
            //
        }

        /**
         * Page for editing data.
         *
         * @param string $id
         * @return view
         */
        public function edit($id)
        {
            //
        }

        /**
         * Function to update data.
         *
         * @param string $id
         * @return response
         */
        public function update($id)
        {
            //
        }

        /**
         * Function to delete data.
         *
         * @param string $id
         * @return response
         */
        public function destroy($id)
        {
            //
        }
    PHP;
    }

    $constructorsCode = '';

    foreach ($constructors as $constructor) {
      $constructorsCode .= '        ' . $constructor . PHP_EOL;
    }

    $print = str_replace(
      [
        '@@CLASS_NAME@@',
        '@@TITLE@@',
        '@@CONSTRUCTORS@@',
        '@@RESOURCE_METHODS@@',
      ],
      [
        $name,
        $this->str->parseTitle($name),
        $constructorsCode,
        $resourceMethods,
      ],
      self::TEMPLATE
    );

    return $print;
  }

  const TEMPLATE = <<<'PHP'
<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_Controller;

class @@CLASS_NAME@@ extends MY_Controller
{
    /**
     * Page title.
     *
     * @var string
     */
    private $title;

    public function __construct()
    {
        parent::__construct();

        $this->title = '@@TITLE@@';
@@CONSTRUCTORS@@
    }

    /**
     * Index page.
     *
     * @return view
     */
    public function index()
    {
        $data = [
            'title' => $this->title,
        ];

        return $this->withData($data)->asView('layout/wrapper');
    }
@@RESOURCE_METHODS@@
    /**
     * Function for datatables.
     *
     * @return string JSON
     */
    public function datatables()
    {
        $draw = $this->input->post('draw') ?: 1;
        $length = $this->input->post('length') ?: 10;
        $start = $this->input->post('start') ?: 0;
        $search = $this->input->post('search')
            ? strtolower($this->input->post('search'))
            : null;

        // $columnIndex = $this->input->post('order')[0]['column'];
        // $columnName = $this->input->post('columns')[$columnIndex]['data'];
        // $columnSortOrder = $this->input->post('order')[0]['dir'];

        // Your datatables query here.
        // $datatables = [];
        // $totalRecordsWithFilter = 0;
        // $totalRecords = 0;

        $return = [
            'status' => TRUE,
            'message' => 'Data ditemukan',
            'draw' => intval($draw),
            // 'aaData' => $datatables,
            // 'iTotalDisplayRecords' => $totalRecordsWithFilter,
            // 'iTotalRecords' => $totalRecords,
        ];

        return $this->asJson($return);
    }
}
PHP;
}
