<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;
use Behat\Gherkin\GherkinCompatibilityMode;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withFormatter(new Pretty_Formatter(
            timer: false,
            paths: false,
        ))
    )->withProfile(
        (new Profile('gherkin-32'))
        ->withGherkinOptions(
            (new Gherkin_Options())
            ->withCompatibilityMode(GherkinCompatibilityMode::GHERKIN_32)
            ->withCacheDir('/tmp/gc')
        )
    )
;
