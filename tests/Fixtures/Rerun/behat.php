<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_suite(
                (new Suite('default'))
                    ->with_paths('features/apples.feature', 'features/bananas.feature')
                    ->with_contexts('Feature_Context')
            )
            ->with_suite(
                (new Suite('suite2'))
                    ->with_paths('features/bananas.feature')
                    ->with_contexts('Feature_Context')
            )
    )
;
