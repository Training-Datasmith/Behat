<?php

declare(strict_types=1);

/**
 * Restore parent::method() names for third-party parents using upstream file pairs.
 */
$root = dirname(__DIR__);

/** @var array<string, string> $pathMap newPath => oldPath */
$pathMap = [];
$renameOutput = shell_exec('git -C ' . escapeshellarg($root) . ' show --name-status --format= b5a9dc6b');
if (!is_string($renameOutput)) {
    fwrite(STDERR, "Could not read rename commit\n");
    exit(1);
}
foreach (explode("\n", trim($renameOutput)) as $line) {
    if (!preg_match('/^R\d*\t(.+)\t(.+)$/', $line, $m)) {
        continue;
    }
    $pathMap[$m[2]] = $m[1];
}

$upstream = '5b36302c';
$changed = 0;

foreach ($pathMap as $newPath => $oldPath) {
    if (!str_ends_with($newPath, '.php')) {
        continue;
    }
    $abs = $root . '/' . $newPath;
    if (!is_file($abs)) {
        continue;
    }

    $oldContent = shell_exec('git -C ' . escapeshellarg($root) . ' show ' . escapeshellarg($upstream . ':' . $oldPath));
    if (!is_string($oldContent) || $oldContent === '') {
        continue;
    }

    preg_match_all('/parent::([a-zA-Z0-9_]+)\s*\(/', $oldContent, $oldMatches);
    preg_match_all('/parent::([a-zA-Z0-9_]+)\s*\(/', file_get_contents($abs) ?: '', $newMatches);

    $oldParents = array_unique($oldMatches[1]);
    $newParents = array_unique($newMatches[1]);

    if ($oldParents === [] && $newParents === []) {
        continue;
    }

    $content = file_get_contents($abs) ?: '';
    $original = $content;

    foreach ($newParents as $wrong) {
        if (!str_contains($wrong, '_')) {
            continue;
        }
        $camel = null;
        foreach ($oldParents as $candidate) {
            if (strtolower(preg_replace('/[^a-z]/', '', $candidate)) === strtolower(preg_replace('/[^a-z]/', '', $wrong))) {
                $camel = $candidate;
                break;
            }
        }
        if ($camel === null) {
            continue;
        }
        $content = preg_replace('/parent::' . preg_quote($wrong, '/') . '\b/', 'parent::' . $camel, $content) ?? $content;
    }

    // Restore Symfony / third-party override method names when upstream differs only by case/snake.
    if (preg_match_all('/^\s*(?:public|protected)\s+function\s+([a-zA-Z0-9_]+)\s*\(/m', $oldContent, $oldMethods)
        && preg_match_all('/^\s*(?:public|protected)\s+function\s+([a-zA-Z0-9_]+)\s*\(/m', $content, $newMethods)) {
        foreach ($newMethods[1] as $wrongMethod) {
            if (!str_contains($wrongMethod, '_')) {
                continue;
            }
            foreach ($oldMethods[1] as $oldMethod) {
                if (strtolower(preg_replace('/[^a-z]/', '', $oldMethod)) !== strtolower(preg_replace('/[^a-z]/', '', $wrongMethod))) {
                    continue;
                }
                if ($oldMethod === $wrongMethod) {
                    break;
                }
                $content = preg_replace(
                    '/\bfunction\s+' . preg_quote($wrongMethod, '/') . '\s*\(/',
                    'function ' . $oldMethod . '(',
                    $content
                ) ?? $content;
                break;
            }
        }
    }

    if ($content !== $original) {
        file_put_contents($abs, $content);
        ++$changed;
    }
}

fwrite(STDOUT, "Updated {$changed} files for parent calls / overrides\n");
