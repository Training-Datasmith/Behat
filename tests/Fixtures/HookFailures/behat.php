<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('beforeSuite'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'Before_Suite_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('afterSuite'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'After_Suite_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('beforeFeature'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'Before_Feature_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('afterFeature'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'After_Feature_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('beforeScenario'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'Before_Scenario_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('afterScenario'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'After_Scenario_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('beforeStep'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'Before_Step_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->with_profile(
        (new Profile('afterStep'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts(
                'After_Step_Context',
                'Simple_Step_Context'
            )
        )
    )
;
