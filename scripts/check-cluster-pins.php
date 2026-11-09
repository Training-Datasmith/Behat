<?php

declare(strict_types=1);

/**
 * Fail if any cluster package is not installed at the expected index SHA and root alias.
 * Runs an in-memory wrong-SHA self-test on every invocation.
 */
$expected = [
    'nikic/php-parser' => [
        'alias' => '5.7.99',
        'reference' => '50f0d9c9d0e3cff1163c959c50aaaaa4a7115f08',
    ],
    'psr/container' => [
        'alias' => '2.0.2',
        'reference' => '57d8cac2ff3db652075e2d6e44e0ede70720e8e5',
    ],
    'psr/event-dispatcher' => [
        'alias' => '1.0.99',
        'reference' => '2b4e85d5a882c9a0312aec7be4e8f9774544f230',
    ],
    'psr/log' => [
        'alias' => '3.0.2',
        'reference' => 'bde874f9b44efec5901a6783185c050a3855b0f0',
    ],
    'symfony/polyfill-ctype' => [
        'alias' => '1.33.99',
        'reference' => '178d8180c34087830dbcdb5e67fa8b61d28016ac',
    ],
    'symfony/polyfill-intl-grapheme' => [
        'alias' => '1.33.99',
        'reference' => '137f223b5cb3ba256d8c52adea5e0e8594708e71',
    ],
    'symfony/polyfill-intl-normalizer' => [
        'alias' => '1.33.99',
        'reference' => 'c6839534bfcaf6a8b566e44e5bb971e672a678cd',
    ],
    'symfony/polyfill-mbstring' => [
        'alias' => '1.33.99',
        'reference' => '2cecae254e4ffc2c20ac379c5cd23d91c82109df',
    ],
    'symfony/polyfill-php84' => [
        'alias' => '1.33.99',
        'reference' => '6fd525a55d5b323edce99f17e44c9442df524c97',
    ],
];

$installedPhp = __DIR__ . '/../vendor/composer/installed.php';
if (!is_readable($installedPhp)) {
    fwrite(STDERR, "check-cluster-pins: vendor/composer/installed.php not found; run composer update first.\n");
    exit(1);
}

/** @var array{versions: array<string, array{reference?: string, aliases?: list<string>}>} $installed */
$installed = require $installedPhp;
$versions = $installed['versions'] ?? [];

/**
 * @param array<string, array{alias: string, reference: string}> $map
 *
 * @return list<string>
 */
function checkClusterPins(array $map, array $versions): array
{
    $errors = [];

    foreach ($map as $name => $want) {
        if (!isset($versions[$name])) {
            $errors[] = sprintf('%s is not installed.', $name);
            continue;
        }

        $pkg = $versions[$name];
        $aliases = $pkg['aliases'] ?? [];
        if (!in_array($want['alias'], $aliases, true)) {
            $errors[] = sprintf(
                '%s alias mismatch (expected %s in aliases %s).',
                $name,
                $want['alias'],
                json_encode($aliases)
            );
        }

        $reference = $pkg['reference'] ?? null;
        if (!is_string($reference) || $reference === '') {
            $errors[] = sprintf('%s has no reference.', $name);
            continue;
        }

        if (!hash_equals($want['reference'], $reference)) {
            $errors[] = sprintf(
                '%s reference mismatch (expected %s, got %s).',
                $name,
                $want['reference'],
                $reference
            );
        }
    }

    return $errors;
}

$wrongDemo = $expected;
$wrongDemo['nikic/php-parser']['reference'] = '0000000000000000000000000000000000000000';
$demoErrors = checkClusterPins($wrongDemo, $versions);
if ($demoErrors === []) {
    fwrite(STDERR, "check-cluster-pins: wrong-SHA self-test failed to detect a bad reference.\n");
    exit(1);
}

$hasParserMismatch = false;
foreach ($demoErrors as $error) {
    if (str_contains($error, 'nikic/php-parser') && str_contains($error, 'reference mismatch')) {
        $hasParserMismatch = true;
        break;
    }
}

if (!$hasParserMismatch) {
    fwrite(STDERR, "check-cluster-pins: wrong-SHA self-test did not report nikic/php-parser reference mismatch.\n");
    fwrite(STDERR, implode("\n", $demoErrors) . "\n");
    exit(1);
}

fwrite(STDOUT, "check-cluster-pins: wrong-SHA self-test detected expected nikic/php-parser mismatch.\n");

$errors = checkClusterPins($expected, $versions);
if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'check-cluster-pins: ' . $error . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "check-cluster-pins: all cluster packages match expected SHAs and aliases.\n");
exit(0);
