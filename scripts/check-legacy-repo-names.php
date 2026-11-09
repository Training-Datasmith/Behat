<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$listFile = $root . '/scripts/legacy-rename-basenames.txt';
if (!is_readable($listFile)) {
    fwrite(STDERR, "check-legacy-repo-names: missing scripts/legacy-rename-basenames.txt\n");
    exit(1);
}

/** @var list<string> $legacyBasenames */
$legacyBasenames = array_values(array_filter(array_map('trim', file($listFile) ?: [])));

$scanRoots = [$root . '/src', $root . '/tests', $root . '/features', $root . '/bin'];
$configFiles = [
    $root . '/behat.yml.dist',
    $root . '/behat.yaml.dist',
    $root . '/phpstan.dist.neon',
    $root . '/rector.php',
    $root . '/.php-cs-fixer.dist.php',
];

$patternSpecs = [];
foreach ($legacyBasenames as $basename) {
    $q = preg_quote($basename, '/');
    $patternSpecs[] = ['basename' => $basename, 'pattern' => '/\buse\s+[^;]*\\\\' . $q . '\s*;/'];
    $patternSpecs[] = ['basename' => $basename, 'pattern' => '/\b(?:extends|implements)\s+' . $q . '\b/'];
    $patternSpecs[] = ['basename' => $basename, 'pattern' => '/\bnew\s+' . $q . '\s*\(/'];
    $patternSpecs[] = ['basename' => $basename, 'pattern' => '/\b' . $q . '::/'];
}

$errors = [];
$scan = static function (string $rel, string $content) use (&$errors, $patternSpecs, $legacyBasenames): void {
    $gherkinShortNames = [];
    if (preg_match_all('/^use\s+Behat\\\\Gherkin\\\\[^;]*\\\\([A-Za-z0-9_]+)\s*;/m', $content, $importMatches)) {
        foreach ($importMatches[1] as $short) {
            $gherkinShortNames[$short] = true;
        }
    }
    if (str_ends_with($rel, '.php')) {
        foreach ($legacyBasenames as $basename) {
            if (str_ends_with($rel, $basename . '.php')) {
                return;
            }
        }
    }
    foreach ($patternSpecs as $spec) {
        $pattern = $spec['pattern'];
        $basename = $spec['basename'];
        if (!preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            continue;
        }
        if (isset($gherkinShortNames[$basename])) {
            continue;
        }
        $offset = $match[0][1];
        $lineStart = strrpos(substr($content, 0, $offset), "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;
        $lineEnd = strpos($content, "\n", $offset);
        $line = $lineEnd === false ? substr($content, $lineStart) : substr($content, $lineStart, $lineEnd - $lineStart);
        if (preg_match('/^\s*use\s+(?:Symfony|Composer|Psr|PHPUnit|Doctrine|PhpParser|Behat\\\\Gherkin)\\\\/i', $line) === 1) {
            continue;
        }
        $errors[] = sprintf('%s matches legacy pattern %s', $rel, $pattern);

        return;
    }
};

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (str_contains($path, '/vendor/')) {
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }
        $scan(substr($path, strlen($root) + 1), $content);
    }
}

foreach ($configFiles as $configPath) {
    if (!is_file($configPath)) {
        continue;
    }
    $scan(substr($configPath, strlen($root) + 1), file_get_contents($configPath) ?: '');
}

if ($errors !== []) {
    foreach (array_slice($errors, 0, 50) as $error) {
        fwrite(STDERR, 'check-legacy-repo-names: ' . $error . "\n");
    }
    if (count($errors) > 50) {
        fwrite(STDERR, 'check-legacy-repo-names: ... and ' . (count($errors) - 50) . " more\n");
    }
    exit(1);
}

fwrite(STDOUT, "check-legacy-repo-names: no pre-rename repo-owned identifiers found.\n");
exit(0);
