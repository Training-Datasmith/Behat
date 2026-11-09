<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context')
    )
;

$progressNoOutputProfile = (new Profile('progress_no_output'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context')
    )
    ->with_formatter(new Progress_Formatter(showOutput: Show_Output_Option::No))
;

return (new Config())
    ->with_profile($defaultProfile)
    ->with_profile($progressNoOutputProfile)
;
