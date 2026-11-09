<?php

declare(strict_types=1);

/**
 * Restore upstream Symfony / Console method names on third-party objects.
 */
$methodMap = [
    'set_parameter' => 'setParameter',
    'get_parameter' => 'getParameter',
    'set_definition' => 'setDefinition',
    'get_definition' => 'getDefinition',
    'add_option' => 'addOption',
    'add_argument' => 'addArgument',
    'get_option' => 'getOption',
    'get_argument' => 'getArgument',
    'get_parameter_option' => 'getParameterOption',
    'replace_argument' => 'replaceArgument',
    'set_synthetic' => 'setSynthetic',
    'set_public' => 'setPublic',
    'add_method_call' => 'addMethodCall',
    'add_tag' => 'addTag',
    'get_method_calls' => 'getMethodCalls',
    'set_method_calls' => 'setMethodCalls',
    'get_formatter' => 'getFormatter',
    'set_decorated' => 'setDecorated',
];

$varPrefixes = ['$container', '$command', '$input', '$output', '$definition', '$formatter', '$translator', '$printer'];

$roots = [__DIR__ . '/../src', __DIR__ . '/../tests'];
$changed = 0;

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }
        $original = $content;
        foreach ($methodMap as $from => $to) {
            $content = preg_replace(
                '/(\$(?:container|command|input|output|definition|loader_definition|tree_builder|builder|root_node|gherkin)(?:->[a-zA-Z_][a-zA-Z0-9_]*)*)->' . preg_quote($from, '/') . '\(/',
                '$1->' . $to . '(',
                $content
            ) ?? $content;
        }
        if ($content !== $original) {
            file_put_contents($path, $content);
            ++$changed;
        }
    }
}

fwrite(STDOUT, "fix-symfony-di-calls: updated {$changed} files\n");
