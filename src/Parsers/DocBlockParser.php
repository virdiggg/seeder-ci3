<?php

namespace Virdiggg\SeederCi3\Parsers;

class DocBlockParser
{
    /**
     * Split a docblock into summary, description and tags.
     *
     * @param string|null $doc
     *
     * @return array ['summary' => string, 'description' => string, 'tags' => [name => string[]]]
     */
    public function parse($doc)
    {
        $result = ['summary' => '', 'description' => '', 'tags' => []];

        if (!$doc) {
            return $result;
        }

        $text = preg_replace('#^\s*/\*\*|\*/\s*$#', '', trim($doc));
        $lines = [];

        foreach (preg_split('/\R/', $text) as $line) {
            $lines[] = rtrim(preg_replace('/^\s*\*? ?/', '', $line));
        }

        $prose = [];
        $currentTag = null;
        $currentIndex = 0;

        foreach ($lines as $line) {
            if (preg_match('/^@([\w\-]+)\s*(.*)$/', $line, $m)) {
                $currentTag = strtolower($m[1]);
                $result['tags'][$currentTag][] = trim($m[2]);
                $currentIndex = count($result['tags'][$currentTag]) - 1;
                continue;
            }

            if ($currentTag !== null) {
                if ($line !== '') {
                    $result['tags'][$currentTag][$currentIndex] .= ' ' . trim($line);
                }
                continue;
            }

            $prose[] = $line;
        }

        $paragraphs = preg_split('/\n\s*\n/', trim(implode("\n", $prose)));
        $summary = array_shift($paragraphs);

        $result['summary'] = trim(preg_replace('/\s+/', ' ', (string) $summary));
        $result['description'] = trim(implode("\n\n", array_map('trim', $paragraphs)));

        return $result;
    }
}
