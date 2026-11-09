<?php

use Behat\Config\Config;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('small_kid'))
            ->withPaths(
                'features/multiple_suites_1.feature',
                'features/multiple_suites_2.feature'
            )
            ->withFilter(
                new Role_Filter('small kid')
            )
        )
        ->withSuite(
            (new Suite('old_man'))
            ->withPaths(
                'features/multiple_suites_1.feature',
                'features/multiple_suites_2.feature'
            )
            ->withFilter(
                new Role_Filter('old man')
            )
        )
    );
