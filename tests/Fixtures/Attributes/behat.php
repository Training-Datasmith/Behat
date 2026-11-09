<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('step_attributes'))
        ->withSuite(new Suite('default', ['contexts' => ['Step_Attributes_Context']]))
    )
    ->withProfile(
        (new Profile('feature_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['Feature_Hook_Attributes_Context']]))
    )
    ->withProfile(
        (new Profile('scenario_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['Scenario_Hook_Attributes_Context']]))
    )
    ->withProfile(
        (new Profile('suite_hooks'))
        ->withSuite(new Suite('apples', ['contexts' => ['Suite_Hook_Attributes_Context']]))
    )
    ->withProfile(
        (new Profile('step_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['Step_Hook_Attributes_Context']]))
    );
