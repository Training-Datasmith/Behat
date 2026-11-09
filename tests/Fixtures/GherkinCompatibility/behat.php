<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;
use Behat\Gherkin\GherkinCompatibilityMode;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_formatter(new Pretty_Formatter(
            timer: false,
            paths: false,
        ))
    )->with_profile(
        (new Profile('gherkin-32'))
        ->with_gherkin_options(
            (new Gherkin_Options())
            ->with_compatibility_mode(GherkinCompatibilityMode::GHERKIN_32)
            ->with_cache_dir('/tmp/gc')
        )
    )
;
