<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Templates\ControllerTemplate;
use Virdiggg\SeederCi3\Utils\{File, Str};

class MakeControllerCommand extends Command
{
    protected $input;
    protected $env;
    protected $fl;
    protected $str;
    protected $config;

    public function __construct($input, $env)
    {
        $this->input = $input;
        $this->env = $env;
        $this->fl = new File();
        $this->str = new Str();
        $this->config = $env->getConfig();
    }

    public function handle()
    {
        try {
            $name = $this->input->argument(0);

            if (!$name) {
                throw new \Exception('Controller name required');
            }

            $_params = $this->parsingParams($name);

            if (file_exists($_params['path'])) {
                throw new \Exception('Controller already exists');
            }

            file_put_contents($_params['path'], $_params['content']);

            echo "Controller created: " . $this->str->greenText($_params['path']);
        } catch (\Throwable $th) {
            echo $this->str->redText($th->getMessage());
            return;
        }
    }

    private function parsingParams($name) {
        $params = $this->input->options();

        $name = trim($name, '/');

        $parts = explode('/', $name);

        $parts = array_map(function ($part) {
            return ucfirst(strtolower($part));
        }, $parts);

        $className = end($parts);

        $relativePath = implode(DIRECTORY_SEPARATOR, $parts);

        $constructors = $this->config->constructors['controller'] ?? [];

        $template = new ControllerTemplate();

        $content = $template->template($className, $params, $constructors);

        $path = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . $relativePath . '.php';

        $this->fl->ensureDirectoryExists(dirname($path));

        return [
            'path' => $path,
            'content' => $content,
            'name' => $name,
        ];
    }
}