<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Tester_Options;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_tester_options(
                (new Tester_Options())->with_print_behat_deprecations()
            )
            ->with_suite(
                (new Suite('default'))
                    ->with_paths('features/deprecations_in_steps.feature')
            )
    );
