#!/usr/bin/env php
<?php

$files = array_merge(
    glob(__DIR__.'/*.rst'),
    glob(__DIR__.'/**/*.rst')
);

foreach ($files as $filePath) {
    $originalContents = file_get_contents($filePath);
    $modifiedContents = preg_replace_callback(
        '/^(?<indent>[ \t]*)(?<directive>\.\.\sconfiguration-block::\n)$\n(?<content>.*)^(?=\1[^\s]+)/Ums',
        function ($matches) {
            foreach ((array) $matches['content'] as $match) {
                $containsHtmlPhp = false !== strpos($match, 'html+php');
                $containsHtmlTwig = false !== strpos($match, 'html+twig');
                $containsHtmlJinja = false !== strpos($match, 'html+jinja');
                $containsTwig = false !== strpos($match, 'twig');

                if (!$containsHtmlPhp || (!$containsHtmlTwig && !$containsTwig && !$containsHtmlJinja)) {
                    var_dump($match);
                    return sprintf("%s%s\n%s", $matches['indent'], $matches['directive'], $match);
                }

                $match = unindent($match);

                preg_match('/^(?<directive>\.\.\scode-block:: (html\+)?(twig|jinja)\n)$\n(?<twig>.*)^(?=[^\s]+)/Ums', $match, $subMatches);

                return sprintf("%s\n%s", $subMatches['directive'], $subMatches['twig']);
            }
        },
        $originalContents
    );

    if ($originalContents !== $modifiedContents) {
        // file_put_contents('./original.txt', $originalContents);
        // file_put_contents('./modified.txt', $modifiedContents);
        file_put_contents($filePath, $modifiedContents);
    }

    continue;



    if (false !== preg_match_all('/^([ \t]*)(\.\.\sconfiguration-block::\n)$\n(?<content>.*)^(?=\1[^\s]+)/Ums', $contents, $matches)) {
        foreach ($matches['content'] as $match) {
            if (false === strpos($match, 'html+php') && false === strpos($match, 'html+twig')) {
                continue;
            }

            $match = unindent($match);

            preg_match_all('/^(\.\.\scode-block:: html\+twig\n)$\n(?<twig>.*)^(?=[^\s]+)/Ums', $match, $subMatches);

            $twigCode = $subMatches['twig'][0];

            return sprintf(".. code-block:: html+twig\n\n%s", $twigCode);
        }
    }
}

function unindent($string)
{
    $lines = explode("\n", $string);
    preg_match('/(\s+)[^\s]*/', $lines[0], $matches);
    $numSpacesOfFirstLine = strlen($matches[0]) - 1;

    $unindentedLines = array_map(function ($line) use ($numSpacesOfFirstLine) {
        return substr($line, $numSpacesOfFirstLine - 1);
    }, $lines);

    $unindentedString = implode("\n", $unindentedLines);

    return $unindentedString;
}
