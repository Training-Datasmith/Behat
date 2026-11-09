<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('beforeSuite'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'Before_Suite_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('afterSuite'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'After_Suite_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('beforeFeature'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'Before_Feature_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('afterFeature'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'After_Feature_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('beforeScenario'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'Before_Scenario_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('afterScenario'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'After_Scenario_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('beforeStep'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'Before_Step_Context',
                'Simple_Step_Context'
            )
        )
    )
    ->withProfile(
        (new Profile('afterStep'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts(
                'After_Step_Context',
                'Simple_Step_Context'
            )
        )
    )
;
