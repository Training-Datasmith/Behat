<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile(
        (new Profile('pretty_with_long_summary'))
        ->withFormatter(new Pretty_Formatter(
            shortSummary: false,
            timer: false
        ))
    )
    ->withProfile(
        (new Profile('progress_with_short_summary'))
        ->withFormatter(new Progress_Formatter(
            shortSummary: true,
            timer: false
        ))
    )
;
