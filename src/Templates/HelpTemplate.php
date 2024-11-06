<?php

namespace Virdiggg\SeederCi3\Templates;
use Virdiggg\SeederCi3\Helpers\StrHelper as Str;

class HelpTemplate
{
    private $str;
    public function __construct()
    {
        $this->str = new Str();
    }

    /**
     * Print help options.
     *
     * @return void
     */
    public function template()
    {
        $arr = [
            [
                'label' => 'Open help options',
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
                'cmd' => 'php index.php app seed [table_name] [--limit=number]',
            ],
            [
                'label' => 'To create migration file based on table',
                'cmd' => 'php index.php app migration [table_name] [--soft-delete] ',
            ],
            [
                'label' => 'To create model file',
                'cmd' => 'php index.php app model [dir/model_name] [--r] [--c] [--m] [--soft-delete]',
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
}