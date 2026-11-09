<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withFormatter(new Pretty_Formatter(
            timer: false,
            paths: false,
        ))
    )
    ->withProfile(
        (new Profile('hide_skipped'))
        ->withFormatter(new Pretty_Formatter(
            printSkippedSteps: false,
            timer: false,
            paths: false,
        ))
    )
;
