<?php

namespace Virdiggg\SeederCi3;

class MY_Controller extends \CI_Controller
{
    protected bool $jsonPretty = false;
    protected array $data = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function pretty($state = false) {
        $this->jsonPretty = (bool) $state;
        return $this;
    }

    protected function asJson($res)
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($this->jsonPretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        echo json_encode($res, $flags);

        return;
    }

    protected function withData($data = []) {
        $this->data = (array) $data;
        return $this;
    }

    protected function asView($view, $data = [])
    {
        if (!empty($this->data)) {
            $data = array_merge($data, $this->data);
        }
        return $this->load->view($view, $data);
    }

    protected function asHtml($view, $data = [])
    {
        if (!empty($this->data)) {
            $data = array_merge($data, $this->data);
        }
        return $this->load->view($view, $data, true);
    }
}