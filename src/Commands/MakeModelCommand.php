<?php

namespace Virdiggg\SeederCi3\Commands;

use Virdiggg\SeederCi3\Console\Command;
use Virdiggg\SeederCi3\Templates\ModelTemplate;
use Virdiggg\SeederCi3\Utils\{File, Str};
use Virdiggg\SeederCI3\Console\Kernel;

class MakeModelCommand extends Command
{
    protected $input;
    protected $env;
    protected $kernel;
    protected $fl;
    protected $str;

    public function __construct($input, $env)
    {
        $this->input = $input;
        $this->env = $env;
        $this->config = $env->getConfig();
        $this->fl = new File();
        $this->str = new Str();
        $this->kernel = new Kernel($this->env);
    }

    public function handle()
    {
        try {
            $name = $this->input->argument(0);

            if (!$name) {
                throw new \Exception('Model name required');
            }

            $dbConn = $this->input->option('db') ?? $this->config->dbConn;

            $databases = $this->config->databases ?? [];
            if (!isset($databases[$dbConn])) {
                throw new \Exception('Database connection not found: ' . $dbConn);
            }

            $_params = $this->parsingParams($dbConn, $databases, $name);

            if (file_exists($_params['path'])) {
                throw new \Exception('Model already exists');
            }

            file_put_contents($_params['path'], $_params['content']);

            echo "Model created: " . $this->str->greenText($_params['path']);

            $this->postEvents($_params['name'], $_params['className']);
        } catch (\Throwable $th) {
            echo $this->str->redText($th->getMessage());
            return;
        }
    }

    private function parsingParams($dbConn, $databases, $name) {
        $driver = $databases[$dbConn]['dbdriver'] ?? 'mysqli';

        $params = $this->input->options();

        $name = trim($name, '/');

        $parts = explode('/', $name);

        $parts = array_map(function ($part) {
            return ucfirst(strtolower($part));
        }, $parts);

        $className = end($parts);

        $relativePath = implode(DIRECTORY_SEPARATOR, $parts);

        $constructors = $this->config->constructors['model'] ?? [];

        $template = new ModelTemplate($driver);

        $content = $template->template($className, $params, $constructors);

        $path = APPPATH . 'models' . DIRECTORY_SEPARATOR . $relativePath . '.php';

        $this->fl->ensureDirectoryExists(dirname($path));

        return [
            'path' => $path,
            'content' => $content,
            'name' => $name,
            'className' => $className,
        ];
    }

    private function postEvents($name, $className)
    {
        if ($this->input->option('c')) {
            $forwardOptions = ['r'];

            $controllerArgs = [$name];

            foreach ($forwardOptions as $option) {
                $value = $this->input->option($option);

                if ($value === true) {
                    $controllerArgs[] = '--' . $option;
                } elseif ($value !== null) {
                    $controllerArgs[] = '--' . $option . '=' . $value;
                }
            }

            $this->kernel->run('make:controller', $controllerArgs);
        }

        if ($this->input->option('m')) {
            $forwardOptions = ['db', 'soft-delete'];

            $migrationArgs = [$className];

            foreach ($forwardOptions as $option) {
                $value = $this->input->option($option);

                if ($value === true) {
                    $migrationArgs[] = '--' . $option;
                } elseif ($value !== null) {
                    $migrationArgs[] = '--' . $option . '=' . $value;
                }
            }

            $this->kernel->run('make:migration', $migrationArgs);
        }

        if ($this->input->option('faker')) {
            $fakerArgs = [$name];

            $this->kernel->run('make:faker', $fakerArgs);
        }
    }
}