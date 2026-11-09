<?php

use Behat\Config\Config;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(
            (new Suite('small_kid'))
            ->with_paths(
                'features/multiple_suites_1.feature',
                'features/multiple_suites_2.feature'
            )
            ->with_filter(
                new Role_Filter('small kid')
            )
        )
        ->with_suite(
            (new Suite('old_man'))
            ->with_paths(
                'features/multiple_suites_1.feature',
                'features/multiple_suites_2.feature'
            )
            ->with_filter(
                new Role_Filter('old man')
            )
        )
    );
