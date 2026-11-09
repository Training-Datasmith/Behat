<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_formatter(new Pretty_Formatter(
            timer: false,
            paths: false,
        ))
    )
    ->with_profile(
        (new Profile('hide_skipped'))
        ->with_formatter(new Pretty_Formatter(
            printSkippedSteps: false,
            timer: false,
            paths: false,
        ))
    )
;
