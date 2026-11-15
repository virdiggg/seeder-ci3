<?php

namespace Virdiggg\SeederCi3\Parsers;

/**
 * Turns "route + controller method" into a documented endpoint.
 *
 * Sources, from weakest to strongest:
 *  1. input->get/post/input_stream/get_post, $_GET, $_POST, $_FILES calls
 *  2. form_validation set_rules() (adds fields, required, type, limits)
 *  3. docblock annotations (@query, @body, @response, @tag, @auth ...)
 */
class EndpointAnalyzer
{
    /** @var InputParser */
    private $inputParser;

    /** @var DocBlockParser */
    private $docParser;

    public function __construct($inputParser = null, $docParser = null)
    {
        $this->inputParser = $inputParser ?: new InputParser();
        $this->docParser = $docParser ?: new DocBlockParser();
    }

    /**
     * @param array      $route  Row from RouteParser (uri, method, controller, action ...)
     * @param array|null $method Result of MethodParser::parse(), null if unresolved
     * @param array      $extra  Target segments after the method, e.g. ['$1']
     *
     * @return array
     */
    public function analyze(array $route, $method, array $extra = [])
    {
        $doc = $this->docParser->parse($method ? $method['doc'] : null);
        $args = $method ? $method['args'] : [];
        $code = $method ? $method['body'] : '';
        $verb = strtoupper($route['method']);

        list($path, $pathParams) = $this->buildPath($route['uri'], $extra, $args, $doc);

        $query = [];
        $body = [];
        $found = $this->inputParser->parse($code);

        foreach ($found['get'] as $name) {
            $this->field($query, $name);
        }
        foreach ($found['post'] as $name) {
            $this->field($body, $name);
        }
        foreach ($this->matchAll($code, '/input->input_stream\s*\(\s*[\'"](.+?)[\'"]/') as $name) {
            $this->field($body, $name);
        }
        $hasFormInputs = count($body) > 0;

        foreach ($this->matchAll($code, '/input->(?:get_post|post_get)\s*\(\s*[\'"](.+?)[\'"]/') as $name) {
            if ($verb === 'GET' || $verb === 'HEAD') {
                $this->field($query, $name);
            } else {
                $this->field($body, $name);
                $hasFormInputs = true;
            }
        }

        $files = array_merge(
            $this->matchAll($code, '/\$_FILES\s*\[\s*[\'"](.+?)[\'"]/'),
            $this->matchAll($code, '/->do_upload\s*\(\s*[\'"](.+?)[\'"]/')
        );
        foreach ($files as $name) {
            $file = &$this->field($body, $name);
            $file['schema'] = ['type' => 'string', 'format' => 'binary'];
            unset($file);
        }

        $rawJson = (bool) preg_match('#php://input|raw_input_stream#', $code);

        // form_validation: fields are often only visible through set_rules().
        $rulesTarget = preg_match('/set_data\s*\(\s*\$_GET|set_data\s*\(\s*\$this->input->get\s*\(\s*\)/', $code)
            ? 'query'
            : 'body';

        foreach ($this->parseRules($code) as $name => $rules) {
            $name = $this->cleanName($name, $isArray);
            if (isset($query[$name])) {
                $target = &$query;
            } elseif (isset($body[$name])) {
                $target = &$body;
            } elseif ($rulesTarget === 'query') {
                $target = &$query;
            } else {
                $target = &$body;
            }

            $this->field($target, $name . ($isArray ? '[]' : ''));
            $this->applyRules($target[$name], $rules);
            unset($target);
        }

        // Docblock annotations win over everything inferred.
        $this->applyAnnotations($query, $doc, 'query');
        $this->applyAnnotations($body, $doc, 'body');

        // Routes without an explicit verb default to GET; a GET that reads
        // request bodies is almost certainly a POST.
        $assumedVerb = false;
        $hasBody = count($body) > 0;
        if ($verb === 'GET' && $hasBody) {
            $verb = 'POST';
            $assumedVerb = true;
        }

        $bodyInfo = null;
        $hasFiles = count($files) > 0;

        if ($hasFiles) {
            $bodyInfo = ['content_type' => 'multipart/form-data', 'fields' => array_values($body)];
        } elseif ($rawJson && !$hasFormInputs && ($hasBody || in_array($verb, ['POST', 'PUT', 'PATCH', 'DELETE'], true))) {
            $bodyInfo = ['content_type' => 'application/json', 'fields' => array_values($body)];
        } elseif ($hasBody) {
            $bodyInfo = ['content_type' => 'application/x-www-form-urlencoded', 'fields' => array_values($body)];
        }

        $tags = [];
        foreach (isset($doc['tags']['tag']) ? $doc['tags']['tag'] : [] as $line) {
            foreach (explode(',', $line) as $tag) {
                if (trim($tag) !== '') {
                    $tags[] = trim($tag);
                }
            }
        }

        $summary = isset($doc['tags']['summary'][0]) ? $doc['tags']['summary'][0] : $doc['summary'];

        return [
            'hidden' => isset($doc['tags']['hidden']) || isset($doc['tags']['internal']),
            'resolved' => $method !== null,
            'method' => $verb,
            'assumed_verb' => $assumedVerb,
            'path' => $path,
            'operation_id' => $route['controller'] . '_' . $route['action'],
            'tags' => $tags ?: [$route['controller']],
            'summary' => $summary !== '' ? $summary : $route['controller'] . ' ' . $route['action'],
            'description' => $doc['description'],
            'deprecated' => isset($doc['tags']['deprecated']),
            'path_params' => $pathParams,
            'query' => array_values($query),
            'body' => $bodyInfo,
            'responses' => $this->responses($code, $doc),
            'content_types' => $this->contentTypes($code),
            'auth' => isset($doc['tags']['auth'][0]) ? strtolower($doc['tags']['auth'][0]) : null,
        ];
    }

    // ------------------------------------------------------------------
    // Path
    // ------------------------------------------------------------------

    /**
     * "users/(:num)/posts/([a-z]+)" + target "…/show/$2/$1" becomes
     * "/users/{id}/posts/{slug}" where names come from the method arguments.
     */
    private function buildPath($uri, array $extra, array $args, array $doc)
    {
        $map = [];
        foreach ($extra as $position => $segment) {
            if (preg_match('/^\$(\d+)$/', $segment, $m) && isset($args[$position])) {
                $map[(int) $m[1]] = $args[$position];
            }
        }

        $docParams = $this->docParams($doc);
        $params = [];
        $used = [];
        $n = 0;

        $path = preg_replace_callback(
            '/\(:num\)|\(:any\)|\((?!:)[^()]*\)/',
            function ($m) use (&$n, &$params, &$used, $map, $docParams) {
                $n++;
                $type = 'string';

                if ($m[0] === '(:num)') {
                    $type = 'integer';
                } elseif ($m[0] !== '(:any)' && preg_match('/\\\\d|\[0-9\]/', $m[0])) {
                    $type = 'integer';
                }

                $arg = isset($map[$n]) ? $map[$n] : null;
                $name = ($arg && $arg['name'] !== '') ? $arg['name'] : 'param' . $n;

                if ($arg && $arg['type']) {
                    $type = $this->mapType($arg['type'], $type);
                }
                if (isset($docParams[$name]['type'])) {
                    $type = $this->mapType($docParams[$name]['type'], $type);
                }

                while (isset($used[$name])) {
                    $name .= '_' . $n;
                }
                $used[$name] = true;

                $params[] = [
                    'name' => $name,
                    'type' => $type,
                    'description' => isset($docParams[$name]['description']) ? $docParams[$name]['description'] : '',
                ];

                return '{' . $name . '}';
            },
            $uri
        );

        return ['/' . ltrim($path, '/'), $params];
    }

    private function docParams(array $doc)
    {
        $result = [];

        foreach (isset($doc['tags']['param']) ? $doc['tags']['param'] : [] as $line) {
            if (preg_match('/^(?:(\S+)\s+)?\$(\w+)\s*(.*)$/', $line, $m)) {
                $result[$m[2]] = ['type' => $m[1] !== '' ? $m[1] : null, 'description' => trim($m[3])];
            }
        }

        return $result;
    }

    private function mapType($type, $fallback)
    {
        switch (strtolower(ltrim($type, '?\\'))) {
            case 'int':
            case 'integer':
                return 'integer';
            case 'float':
            case 'double':
            case 'number':
                return 'number';
            case 'bool':
            case 'boolean':
                return 'boolean';
            case 'string':
                return 'string';
        }

        return $fallback;
    }

    // ------------------------------------------------------------------
    // Fields, rules, annotations
    // ------------------------------------------------------------------

    private function cleanName($name, &$isArray = null)
    {
        $isArray = substr($name, -2) === '[]';

        return $isArray ? substr($name, 0, -2) : $name;
    }

    /**
     * Get or create a field by name. Returns by reference.
     */
    private function &field(array &$list, $name)
    {
        $name = $this->cleanName($name, $isArray);

        if (!isset($list[$name])) {
            $list[$name] = [
                'name' => $name,
                'required' => false,
                'description' => '',
                'schema' => $isArray ? ['type' => 'array', 'items' => ['type' => 'string']] : ['type' => 'string'],
            ];
        }

        return $list[$name];
    }

    private function parseRules($code)
    {
        $rules = [];

        preg_match_all(
            '/set_rules\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"][^\'"]*[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/',
            $code,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $m) {
            $rules[$m[1]] = $m[2];
        }

        preg_match_all(
            '/[\'"]field[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"][^\]]*?[\'"]rules[\'"]\s*=>\s*[\'"]([^\'"]*)[\'"]/s',
            $code,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $m) {
            $rules[$m[1]] = $m[2];
        }

        return $rules;
    }

    private function applyRules(array &$field, $ruleString)
    {
        $target = &$field['schema'];

        // For array fields the constraints belong to the items.
        if (isset($target['type']) && $target['type'] === 'array') {
            $target = &$target['items'];
        }

        foreach (explode('|', $ruleString) as $rule) {
            $arg = null;
            if (preg_match('/^([^\[]+)\[(.*)\]$/', trim($rule), $m)) {
                $rule = $m[1];
                $arg = $m[2];
            }

            switch (trim($rule)) {
                case 'required':
                    $field['required'] = true;
                    break;
                case 'numeric':
                case 'decimal':
                    $target['type'] = 'number';
                    break;
                case 'integer':
                    $target['type'] = 'integer';
                    break;
                case 'is_natural':
                    $target['type'] = 'integer';
                    $target['minimum'] = 0;
                    break;
                case 'is_natural_no_zero':
                    $target['type'] = 'integer';
                    $target['minimum'] = 1;
                    break;
                case 'valid_email':
                    $target['format'] = 'email';
                    break;
                case 'valid_url':
                    $target['format'] = 'uri';
                    break;
                case 'valid_ip':
                    if ($arg === 'ipv4' || $arg === 'ipv6') {
                        $target['format'] = $arg;
                    }
                    break;
                case 'alpha':
                    $target['pattern'] = '^[A-Za-z]+$';
                    break;
                case 'alpha_numeric':
                    $target['pattern'] = '^[A-Za-z0-9]+$';
                    break;
                case 'alpha_dash':
                    $target['pattern'] = '^[A-Za-z0-9_-]+$';
                    break;
                case 'min_length':
                    $target['minLength'] = (int) $arg;
                    break;
                case 'max_length':
                    $target['maxLength'] = (int) $arg;
                    break;
                case 'exact_length':
                    $target['minLength'] = $target['maxLength'] = (int) $arg;
                    break;
                case 'greater_than':
                    $target['minimum'] = $arg + 0;
                    $target['exclusiveMinimum'] = true;
                    break;
                case 'greater_than_equal_to':
                    $target['minimum'] = $arg + 0;
                    break;
                case 'less_than':
                    $target['maximum'] = $arg + 0;
                    $target['exclusiveMaximum'] = true;
                    break;
                case 'less_than_equal_to':
                    $target['maximum'] = $arg + 0;
                    break;
                case 'in_list':
                    $values = array_map('trim', explode(',', (string) $arg));
                    if (isset($target['type']) && in_array($target['type'], ['integer', 'number'], true)) {
                        $values = array_map(function ($v) {
                            return $v + 0;
                        }, $values);
                    }
                    $target['enum'] = $values;
                    break;
            }
        }
    }

    private function applyAnnotations(array &$list, array $doc, $tag)
    {
        foreach (isset($doc['tags'][$tag]) ? $doc['tags'][$tag] : [] as $line) {
            $parsed = $this->parseAnnotation($line);

            if ($parsed === null) {
                continue;
            }

            $field = &$this->field($list, $parsed['name']);
            $field['schema'] = $parsed['schema'] + $field['schema'];
            $field['required'] = $parsed['required'] || $field['required'];

            if ($parsed['description'] !== '') {
                $field['description'] = $parsed['description'];
            }

            unset($field);
        }
    }

    /**
     * "@query int page required Page number" => name, schema, required, description
     */
    private function parseAnnotation($line)
    {
        if (!preg_match('/^(\S+)\s+\$?([\w\[\]\.\-]+)\s*(required\b)?\s*(.*)$/i', $line, $m)) {
            return null;
        }

        $type = strtolower($m[1]);
        $isList = substr($type, -2) === '[]';
        $base = $isList ? substr($type, 0, -2) : $type;

        if ($base === 'file') {
            $schema = ['type' => 'string', 'format' => 'binary'];
        } else {
            $schema = ['type' => $this->mapType($base, 'string')];
        }

        if ($isList) {
            $schema = ['type' => 'array', 'items' => $schema];
        }

        return [
            'name' => $m[2],
            'schema' => $schema,
            'required' => $m[3] !== '',
            'description' => trim($m[4]),
        ];
    }

    // ------------------------------------------------------------------
    // Responses
    // ------------------------------------------------------------------

    private function responses($code, array $doc)
    {
        $responses = [];

        foreach ($this->matchAll($code, '/set_status_header\s*\(\s*(\d{3})/') as $status) {
            $responses[$status] = '';
        }
        foreach ($this->matchAll($code, '/http_response_code\s*\(\s*(\d{3})/') as $status) {
            $responses[$status] = '';
        }
        foreach ($this->matchAll($code, '/show_error\s*\([^;]*?,\s*(\d{3})\s*[,)]/') as $status) {
            $responses[$status] = '';
        }
        if (preg_match('/\bshow_404\s*\(/', $code)) {
            $responses['404'] = '';
        }

        foreach (isset($doc['tags']['response']) ? $doc['tags']['response'] : [] as $line) {
            if (preg_match('/^(\d{3})\s*(.*)$/', $line, $m)) {
                $responses[$m[1]] = trim($m[2]);
            }
        }

        $hasSuccess = false;
        foreach (array_keys($responses) as $status) {
            if (substr((string) $status, 0, 1) === '2') {
                $hasSuccess = true;
            }
        }
        if (!$hasSuccess) {
            $responses['200'] = '';
        }

        ksort($responses);

        return $responses;
    }

    private function contentTypes($code)
    {
        $types = [];

        if (preg_match('/json_encode\s*\(|set_content_type\s*\(\s*[\'"]application\/json/', $code)) {
            $types[] = 'application/json';
        }
        if (preg_match('/load->view\s*\(/', $code)) {
            $types[] = 'text/html';
        }

        return $types;
    }

    private function matchAll($code, $pattern)
    {
        preg_match_all($pattern, $code, $matches);

        return array_values(array_unique($matches[1]));
    }
}
