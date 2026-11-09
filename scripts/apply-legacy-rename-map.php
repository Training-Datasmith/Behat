<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$mapFile = $root . '/scripts/legacy-rename-map.tsv';
if (!is_readable($mapFile)) {
    fwrite(STDERR, "apply-legacy-rename-map: missing map file\n");
    exit(1);
}

/** @var array<string, string> $map */
$map = [];
foreach (file($mapFile) ?: [] as $line) {
    $line = trim($line);
    if ($line === '' || !str_contains($line, "\t")) {
        continue;
    }
    [$old, $new] = explode("\t", $line, 2);
    $map[$old] = $new;
}

$extra = [
    'ServiceContainer' => 'Service_Container',
    'GherkinExtension' => 'Gherkin_Extension',
    'ConfigurationLoadingException' => 'Configuration_Loading_Exception',
];

foreach ($extra as $old => $new) {
    $map[$old] = $new;
}

uksort($map, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

$dirs = ['src', 'tests', 'features'];
$changed = 0;

foreach ($dirs as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }
        $original = $content;
        foreach ($map as $old => $new) {
            $content = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $content) ?? $content;
        }
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            ++$changed;
        }
    }
}

fwrite(STDOUT, "apply-legacy-rename-map: updated {$changed} files\n");
