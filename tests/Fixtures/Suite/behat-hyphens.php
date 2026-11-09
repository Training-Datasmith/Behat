<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->with_suite(
        (new Suite('suite-with-hyphens'))
            ->with_paths('features/little_kid.feature')
            ->with_contexts('Little_Kid_Context')
    )
;

return (new Config())->with_profile($profile);
