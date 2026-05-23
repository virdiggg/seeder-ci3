<?php

namespace Virdiggg\SeederCi3\Console;

abstract class Command
{
    protected $input;
    protected $config;
    protected $kernel;

    public function __construct($input, $config, $kernel)
    {
        $this->input = $input;
        $this->config = $config;
        $this->kernel = $kernel;
    }

    abstract public function handle();
}