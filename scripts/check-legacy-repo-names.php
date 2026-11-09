<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$listFile = $root . '/scripts/legacy-rename-basenames.txt';

if (!is_readable($listFile)) {
    fwrite(STDERR, 'check-legacy-repo-names: missing ' . basename($listFile) . "\n");
    exit(1);
}

/** @var list<string> $legacyBasenames */
$legacyBasenames = array_values(array_filter(array_map('trim', file($listFile) ?: [])));

$scanRoots = [
    $root . '/src',
    $root . '/tests',
    $root . '/features',
    $root . '/bin',
    $root . '/docs',
];

$extraFiles = [
    $root . '/AGENTS.md',
    $root . '/ARCHITECTURE.md',
    $root . '/CONTRIBUTING.md',
    $root . '/README.md',
    $root . '/i18n.php',
    $root . '/behat.yml.dist',
    $root . '/behat.yaml.dist',
    $root . '/phpstan.dist.neon',
    $root . '/rector.php',
    $root . '/.php-cs-fixer.dist.php',
];

$scanExtensions = ['php', 'feature', 'yml', 'yaml', 'md', 'neon', 'dist', 'json'];

/** @var array<string, string> legacy basename => PCRE matching extends/implements of upstream FQCN */
$thirdPartyFqnAllowlist = [
    'TranslatorInterface' => '/\b(?:extends|implements)\s+\\\\Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface\b/',
];

$errors = [];

/**
 * @param list<string> $errors
 */
$scanContent = static function (
    string $rel,
    string $content,
    array &$errors,
    array $legacyBasenames,
    array $thirdPartyFqnAllowlist,
): void {
    $gherkinShortNames = [];
    if (preg_match_all('/^use\s+Behat\\\\Gherkin\\\\[^;]*\\\\([A-Za-z0-9_]+)\s*;/m', $content, $importMatches)) {
        foreach ($importMatches[1] as $short) {
            $gherkinShortNames[$short] = true;
        }
    }

    foreach (explode("\n", $content) as $lineNum => $line) {
        $lineNumber = $lineNum + 1;

        if (preg_match('/^\s*use\s+(?:Symfony|Composer|Psr|PHPUnit|Doctrine|PhpParser|Behat\\\\Gherkin)\\\\/i', $line) === 1) {
            continue;
        }

        if (preg_match('/class_alias\s*\(/', $line) === 1) {
            foreach ($legacyBasenames as $basename) {
                if (preg_match('/[\'"](?:[^\'"]*\\\\)?' . preg_quote($basename, '/') . '[\'"]/', $line) === 1) {
                    $errors[] = sprintf('%s:%d: class_alias references legacy name %s', $rel, $lineNumber, $basename);

                    continue 2;
                }
            }
        }

        foreach ($legacyBasenames as $basename) {
            if (!preg_match('/\b' . preg_quote($basename, '/') . '\b/', $line)) {
                continue;
            }
            if (isset($thirdPartyFqnAllowlist[$basename]) && preg_match($thirdPartyFqnAllowlist[$basename], $line) === 1) {
                continue;
            }
            if (isset($gherkinShortNames[$basename])) {
                continue;
            }
            if (preg_match('/@' . preg_quote($basename, '/') . '(\s|$|\()/ ', $line) === 1) {
                continue;
            }
            if (preg_match('/#\[\\\\?' . preg_quote($basename, '/') . '(\(|::|\s)/', $line) === 1) {
                continue;
            }
            if (preg_match('/https?:\/\/[^\s]*' . preg_quote($basename, '/') . '/', $line) === 1) {
                continue;
            }

            $errors[] = sprintf('%s:%d: legacy repo-owned identifier %s', $rel, $lineNumber, $basename);

            return;
        }
    }
};

$probeContent = "<?php\nuse Behat\\Behat\\ApplicationFactory;\n";
$probeErrors = [];
$scanContent('__probe__.php', $probeContent, $probeErrors, $legacyBasenames, $thirdPartyFqnAllowlist);
if ($probeErrors === []) {
    fwrite(STDERR, "check-legacy-repo-names: built-in detection self-test failed (expected ApplicationFactory probe).\n");
    exit(1);
}

if (in_array('--prove-detection', $argv ?? [], true)) {
    fwrite(STDOUT, 'check-legacy-repo-names: prove-detection OK (' . $probeErrors[0] . ")\n");
    exit(0);
}

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
        if (str_contains($file->getPathname(), '/vendor/')) {
            continue;
        }
        if (!in_array($file->getExtension(), $scanExtensions, true)) {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }
        $scanContent(substr($file->getPathname(), strlen($root) + 1), $content, $errors, $legacyBasenames, $thirdPartyFqnAllowlist);
    }
}

foreach ($extraFiles as $path) {
    if (!is_file($path)) {
        continue;
    }
    $scanContent(substr($path, strlen($root) + 1), file_get_contents($path) ?: '', $errors, $legacyBasenames, $thirdPartyFqnAllowlist);
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
