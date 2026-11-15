<?php

namespace Virdiggg\SeederCi3\Exporters;

/**
 * Builds an OpenAPI 3.0.3 document from endpoints produced by
 * Parsers\EndpointAnalyzer.
 */
class OpenApiExporter
{
    private $phrases = [
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '204' => 'No Content',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '409' => 'Conflict',
        '422' => 'Unprocessable Entity',
        '429' => 'Too Many Requests',
        '500' => 'Internal Server Error',
    ];

    /**
     * @param array $endpoints
     * @param array $options title, version, description, server, auth (bearer|basic|apikey)
     *
     * @return array
     */
    public function export(array $endpoints, array $options = [])
    {
        $options = array_merge([
            'title' => 'API',
            'version' => '1.0.0',
            'description' => '',
            'server' => null,
            'auth' => null,
        ], $options);

        $paths = [];
        $tags = [];
        $ids = [];
        $schemes = [];

        if ($this->scheme($options['auth']) !== null) {
            $schemes[$this->schemeName($options['auth'])] = $this->scheme($options['auth']);
        }

        foreach ($endpoints as $endpoint) {
            if (!empty($endpoint['hidden'])) {
                continue;
            }

            $paths[$endpoint['path']][strtolower($endpoint['method'])] = $this->operation($endpoint, $ids, $schemes);

            foreach ($endpoint['tags'] as $tag) {
                $tags[$tag] = true;
            }
        }

        $info = ['title' => $options['title'], 'version' => $options['version']];
        if ($options['description'] !== '') {
            $info['description'] = $options['description'];
        }

        $spec = ['openapi' => '3.0.3', 'info' => $info];

        if ($options['server']) {
            $spec['servers'] = [['url' => $options['server']]];
        }

        $spec['tags'] = array_map(function ($name) {
            return ['name' => $name];
        }, array_keys($tags));

        if (!$spec['tags']) {
            unset($spec['tags']);
        }

        $spec['paths'] = $paths ?: new \stdClass();

        if ($schemes) {
            $spec['components'] = ['securitySchemes' => $schemes];
        }

        if ($this->scheme($options['auth']) !== null) {
            $spec['security'] = [[$this->schemeName($options['auth']) => []]];
        }

        return $spec;
    }

    private function operation(array $endpoint, array &$ids, array &$schemes)
    {
        $id = $endpoint['operation_id'];
        $suffix = 1;
        while (isset($ids[$id])) {
            $id = $endpoint['operation_id'] . '_' . (++$suffix);
        }
        $ids[$id] = true;

        $description = $endpoint['description'];
        if (!empty($endpoint['assumed_verb'])) {
            $description = trim($description . "\n\nRoute has no HTTP verb; POST was inferred from request body usage.");
        }
        if (empty($endpoint['resolved'])) {
            $description = trim($description . "\n\nController method was not found; only route information is documented.");
        }

        $operation = [
            'tags' => $endpoint['tags'],
            'summary' => $endpoint['summary'],
        ];

        if ($description !== '') {
            $operation['description'] = $description;
        }

        $operation['operationId'] = $id;

        if (!empty($endpoint['deprecated'])) {
            $operation['deprecated'] = true;
        }

        $parameters = [];
        foreach ($endpoint['path_params'] as $param) {
            $parameters[] = $this->parameter($param['name'], 'path', true, $param['description'], ['type' => $param['type']]);
        }
        foreach ($endpoint['query'] as $field) {
            $parameters[] = $this->parameter($field['name'], 'query', $field['required'], $field['description'], $field['schema']);
        }
        if ($parameters) {
            $operation['parameters'] = $parameters;
        }

        if ($endpoint['body']) {
            $operation['requestBody'] = $this->requestBody($endpoint['body']);
        }

        $operation['responses'] = $this->responses($endpoint['responses'], $endpoint['content_types']);

        if ($endpoint['auth'] === 'none') {
            $operation['security'] = [];
        } elseif ($endpoint['auth'] !== null && $this->scheme($endpoint['auth']) !== null) {
            $name = $this->schemeName($endpoint['auth']);
            $schemes[$name] = $this->scheme($endpoint['auth']);
            $operation['security'] = [[$name => []]];
        }

        return $operation;
    }

    private function parameter($name, $in, $required, $description, array $schema)
    {
        $parameter = ['name' => $name, 'in' => $in, 'required' => (bool) $required];

        if ($description !== '') {
            $parameter['description'] = $description;
        }

        $parameter['schema'] = $schema;

        return $parameter;
    }

    private function requestBody(array $body)
    {
        $properties = [];
        $required = [];

        foreach ($body['fields'] as $field) {
            $schema = $field['schema'];

            if ($field['description'] !== '') {
                $schema['description'] = $field['description'];
            }

            $properties[$field['name']] = $schema;

            if ($field['required']) {
                $required[] = $field['name'];
            }
        }

        $schema = ['type' => 'object'];
        if ($properties) {
            $schema['properties'] = $properties;
        }
        if ($required) {
            $schema['required'] = $required;
        }

        return [
            'required' => count($required) > 0,
            'content' => [$body['content_type'] => ['schema' => $schema]],
        ];
    }

    private function responses(array $responses, array $contentTypes)
    {
        $result = [];

        foreach ($responses as $status => $description) {
            $status = (string) $status;
            $entry = [
                'description' => $description !== ''
                    ? $description
                    : (isset($this->phrases[$status]) ? $this->phrases[$status] : 'Response'),
            ];

            if ($status !== '204' && $status[0] !== '3' && $contentTypes) {
                $entry['content'] = [];
                foreach ($contentTypes as $type) {
                    $entry['content'][$type] = [
                        'schema' => ['type' => $type === 'application/json' ? 'object' : 'string'],
                    ];
                }
            }

            $result[$status] = $entry;
        }

        return $result;
    }

    private function schemeName($auth)
    {
        $names = ['bearer' => 'bearerAuth', 'basic' => 'basicAuth', 'apikey' => 'apiKeyAuth'];
        $auth = strtolower((string) $auth);

        return isset($names[$auth]) ? $names[$auth] : null;
    }

    private function scheme($auth)
    {
        switch (strtolower((string) $auth)) {
            case 'bearer':
                return ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'JWT'];
            case 'basic':
                return ['type' => 'http', 'scheme' => 'basic'];
            case 'apikey':
                return ['type' => 'apiKey', 'in' => 'header', 'name' => 'X-API-KEY'];
        }

        return null;
    }
}
