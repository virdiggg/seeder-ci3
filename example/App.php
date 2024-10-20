
<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;

class App extends MY_AppController
{
    public function __construct()
    {
        parent::__construct();
    }

    // If you don't wish to have rollback function
	public function rollback() {
		return;
	}
}