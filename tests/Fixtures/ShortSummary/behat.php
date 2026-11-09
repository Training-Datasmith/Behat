<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('pretty_with_long_summary'))
        ->with_formatter(new Pretty_Formatter(
            shortSummary: false,
            timer: false
        ))
    )
    ->with_profile(
        (new Profile('progress_with_short_summary'))
        ->with_formatter(new Progress_Formatter(
            shortSummary: true,
            timer: false
        ))
    )
;
