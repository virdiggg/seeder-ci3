<?php

namespace Virdiggg\SeederCi3\Console;

class Input
{
  protected $arguments = [];
  protected $options = [];
  protected $rawOptions = [];

  public function __construct(array $argv)
  {
    foreach ($argv as $arg) {
      if (substr($arg, 0, 2) === '--') {
        $option = substr($arg, 2);
        $this->rawOptions[] = $option;
        if (strpos($option, '=') !== false) {
          [$key, $value] = explode('=', $option, 2);
          $this->options[$key] = $value;
        } else {
          $this->options[$option] = true;
        }
      } else {
        $this->arguments[] = $arg;
      }
    }
  }

  public function argument($index, $default = null)
  {
    return $this->arguments[$index] ?? $default;
  }

  public function option($name, $default = null)
  {
    return $this->options[$name] ?? $default;
  }

  public function options()
  {
    return $this->options;
  }

  public function rawOptions()
  {
    return $this->rawOptions;
  }
}
