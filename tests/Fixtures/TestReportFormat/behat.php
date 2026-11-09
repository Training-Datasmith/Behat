<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(
            (new Suite('single_feature'))
            ->with_paths(
                'features/single_feature.feature'
            )
        )
        ->with_suite(
            (new Suite('multiple_features'))
            ->with_paths(
                'features/multiple_features_1.feature',
                'features/multiple_features_2.feature'
            )
        )
        ->with_suite(
            (new Suite('multiline_titles'))
            ->with_paths(
                'features/multiline_titles.feature'
            )
        )
        ->with_suite(
            (new Suite('skipped_test_cases'))
            ->with_paths(
                'features/skipped_test_cases.feature'
            )
        )
        ->with_suite(
            (new Suite('stop_on_failure'))
            ->with_paths(
                'features/stop_on_failure.feature'
            )
        )
        ->with_suite(
            (new Suite('abort_on_php_error'))
            ->with_paths(
                'features/abort_on_php_error.feature'
            )
        )
    );
