<?php

namespace Virdiggg\SeederCi3\Parsers;

/**
 * Token-based controller method reader.
 *
 * Unlike brace counting on raw text, token_get_all() knows what is a string
 * or a comment, so "}" inside a string no longer ends a method early and
 * commented-out $this->input->post('x') calls are not picked up.
 */
class MethodParser
{
    /**
     * Resolve a route target such as "api/v1/User/show/$1" to a controller
     * file, mirroring how CI3's router walks directories.
     *
     * @param string      $target
     * @param string|null $controllersPath
     *
     * @return array|null ['file','class','method','extra']
     */
    public function resolveTarget($target, $controllersPath = null)
    {
        $dir = rtrim($controllersPath ?: APPPATH . 'controllers', '/\\');
        $segments = array_values(array_filter(explode('/', trim($target, '/')), 'strlen'));

        foreach ($segments as $i => $segment) {
            $file = $this->findEntry($dir, $segment, true);

            if ($file !== null) {
                return [
                    'file' => $file,
                    'class' => basename($file, '.php'),
                    'method' => isset($segments[$i + 1]) ? $segments[$i + 1] : 'index',
                    'extra' => array_slice($segments, $i + 2),
                ];
            }

            $dir = $this->findEntry($dir, $segment, false);

            if ($dir === null) {
                return null;
            }
        }

        return null;
    }

    /**
     * Read docblock, arguments and (comment-free) body of a method.
     *
     * @param string $file
     * @param string $method
     *
     * @return array|null ['doc' => string|null, 'args' => array, 'body' => string]
     */
    public function parse($file, $method)
    {
        $code = @file_get_contents($file);

        if ($code === false) {
            return null;
        }

        $tokens = token_get_all($code);
        $count = count($tokens);
        $pendingDoc = null;
        $skippable = [T_WHITESPACE, T_COMMENT, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT];

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_DOC_COMMENT) {
                $pendingDoc = $token[1];
                continue;
            }

            if (is_array($token) && $token[0] === T_FUNCTION) {
                $nameIndex = $this->nextSignificant($tokens, $i + 1);

                // Skip "&" of "function &name()".
                if ($nameIndex !== null && $tokens[$nameIndex] === '&') {
                    $nameIndex = $this->nextSignificant($tokens, $nameIndex + 1);
                }

                if (
                    $nameIndex !== null
                    && is_array($tokens[$nameIndex])
                    && strcasecmp($tokens[$nameIndex][1], $method) === 0
                ) {
                    return $this->readMethod($tokens, $nameIndex, $pendingDoc);
                }

                $pendingDoc = null;
                continue;
            }

            if (!(is_array($token) && in_array($token[0], $skippable, true))) {
                $pendingDoc = null;
            }
        }

        return null;
    }

    private function readMethod(array $tokens, $nameIndex, $doc)
    {
        $count = count($tokens);
        $i = $this->nextSignificant($tokens, $nameIndex + 1);
        $args = [];

        if ($i !== null && $tokens[$i] === '(') {
            $depth = 0;
            $current = [];

            for (; $i < $count; $i++) {
                $t = $tokens[$i];

                if ($t === '(' || $t === '[') {
                    $depth++;
                    if ($depth === 1) {
                        continue;
                    }
                } elseif ($t === ')' || $t === ']') {
                    $depth--;
                    if ($depth === 0) {
                        if ($current) {
                            $args[] = $this->buildArg($current);
                        }
                        $i++;
                        break;
                    }
                } elseif ($t === ',' && $depth === 1) {
                    $args[] = $this->buildArg($current);
                    $current = [];
                    continue;
                }

                $current[] = $t;
            }
        }

        // Skip return type until the body starts (or the method is abstract).
        for (; $i < $count; $i++) {
            if ($tokens[$i] === '{') {
                break;
            }
            if ($tokens[$i] === ';') {
                return ['doc' => $doc, 'args' => $args, 'body' => ''];
            }
        }

        $level = 0;
        $body = '';

        for (; $i < $count; $i++) {
            $t = $tokens[$i];

            if ($t === '{' || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $level++;
                if ($level === 1) {
                    continue;
                }
            } elseif ($t === '}') {
                $level--;
                if ($level === 0) {
                    break;
                }
            }

            if (is_array($t)) {
                if ($t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT) {
                    continue;
                }
                $body .= $t[1];
            } else {
                $body .= $t;
            }
        }

        return ['doc' => $doc, 'args' => $args, 'body' => $body];
    }

    private function buildArg(array $tokens)
    {
        $arg = ['name' => '', 'type' => null, 'has_default' => false, 'default' => null];
        $type = '';
        $default = '';
        $afterEquals = false;

        foreach ($tokens as $t) {
            $text = is_array($t) ? $t[1] : $t;
            $id = is_array($t) ? $t[0] : null;

            if ($id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT) {
                continue;
            }

            if ($afterEquals) {
                $default .= $text;
            } elseif ($t === '=') {
                $afterEquals = true;
                $arg['has_default'] = true;
            } elseif ($id === T_VARIABLE) {
                $arg['name'] = ltrim($text, '$');
            } elseif ($t === '&' || $id === T_ELLIPSIS) {
                continue;
            } elseif ($arg['name'] === '') {
                $type .= $text;
            }
        }

        $arg['type'] = $type !== '' ? ltrim($type, '?\\') : null;
        $arg['default'] = $arg['has_default'] ? $default : null;

        return $arg;
    }

    private function nextSignificant(array $tokens, $from)
    {
        $count = count($tokens);

        for ($i = $from; $i < $count; $i++) {
            $t = $tokens[$i];

            if (is_array($t) && ($t[0] === T_WHITESPACE || $t[0] === T_COMMENT)) {
                continue;
            }

            return $i;
        }

        return null;
    }

    /**
     * Case-insensitive lookup so "storage" finds Storage.php on Linux.
     *
     * @param string $dir
     * @param string $name
     * @param bool   $wantFile true for "Name.php", false for a sub directory
     *
     * @return string|null
     */
    private function findEntry($dir, $name, $wantFile)
    {
        if (!is_dir($dir)) {
            return null;
        }

        $wanted = strtolower($wantFile ? $name . '.php' : $name);

        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..' || strtolower($entry) !== $wanted) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $entry;

            if ($wantFile ? is_file($path) : is_dir($path)) {
                return $path;
            }
        }

        return null;
    }
}
