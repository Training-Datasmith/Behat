<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context')
    )
;

return (new Config())
    ->with_profile($defaultProfile)
;
