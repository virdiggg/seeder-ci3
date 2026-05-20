<?php

namespace Virdiggg\SeederCi3\Parsers;

class ControllerParser
{
    /**
     * Get controller file path.
     *
     * @param string $target
     *
     * @return string|null
     */
    public function resolvePath($target)
    {
        $parts = explode('/', $target);

        if (count($parts) < 2) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | api/Storage/index
        |--------------------------------------------------------------------------
        */
        $controller = $parts[1];

        return APPPATH . 'controllers/api/' . $controller . '.php';
    }

    /**
     * Parse method content.
     *
     * @param string $target
     *
     * @return string|null
     */
    public function parseMethodContent($target) {
        $parts = explode('/', $target);

        if (count($parts) < 3) {
            return null;
        }

        $method = $parts[2];

        $path = $this->resolvePath($target);

        if (!$path || !file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);

        /*
        |--------------------------------------------------------------------------
        | Find method position
        |--------------------------------------------------------------------------
        */
        $pattern = '/public function\s+' . preg_quote($method, '/') . '\s*\(/';

        if (!preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $match[0][1];

        /*
        |--------------------------------------------------------------------------
        | Find first {
        |--------------------------------------------------------------------------
        */
        $braceStart = strpos($content, '{', $start);

        if ($braceStart === false) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Parse braces manually
        |--------------------------------------------------------------------------
        */
        $level = 1;
        $i = $braceStart + 1;
        $length = strlen($content);

        while ($i < $length) {
            $char = $content[$i];

            if ($char === '{') {
                $level++;
            }

            if ($char === '}') {
                $level--;
            }

            if ($level === 0) {
                break;
            }

            $i++;
        }

        return substr($content, $braceStart + 1, $i - $braceStart - 1);
    }
}