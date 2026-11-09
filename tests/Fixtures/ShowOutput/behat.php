<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context')
    )
;

$progressNoOutputProfile = (new Profile('progress_no_output'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context')
    )
    ->withFormatter(new Progress_Formatter(showOutput: Show_Output_Option::No))
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($progressNoOutputProfile)
;
