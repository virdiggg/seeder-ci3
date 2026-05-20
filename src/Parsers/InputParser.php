<?php

namespace Virdiggg\SeederCi3\Parsers;

class InputParser
{
    /**
     * Parse request parameters from method body.
     *
     * @param string $content
     *
     * @return array
     */
    public function parse($content)
    {
        $result = [
            'get' => [],
            'post' => [],
        ];

        if (!$content) {
            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | $this->input->get('id')
        |--------------------------------------------------------------------------
        */
        preg_match_all('/input->get\\s*\\(\\s*[\'"](.+?)[\'"]/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $param) {
                $result['get'][] = $param;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | $_GET['id']
        |--------------------------------------------------------------------------
        */
        preg_match_all('/\\$_GET\\s*\\[\\s*[\'"](.+?)[\'"]\\s*\\]/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $param) {
                $result['get'][] = $param;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | $this->input->post('name')
        |--------------------------------------------------------------------------
        */
        preg_match_all('/input->post\\s*\\(\\s*[\'"](.+?)[\'"]/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $param) {
                $result['post'][] = $param;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | $_POST['name']
        |--------------------------------------------------------------------------
        */
        preg_match_all(
            '/\\$_POST\\s*\\[\\s*[\'"](.+?)[\'"]\\s*\\]/',
            $content,
            $matches
        );

        if (!empty($matches[1])) {
            foreach ($matches[1] as $param) {
                $result['post'][] = $param;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove duplicates
        |--------------------------------------------------------------------------
        */
        $result['get'] = array_values(array_unique($result['get']));

        $result['post'] = array_values(array_unique($result['post']));

        return $result;
    }
}