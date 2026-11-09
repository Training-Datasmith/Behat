<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('step_attributes'))
        ->with_suite(new Suite('default', ['contexts' => ['Step_Attributes_Context']]))
    )
    ->with_profile(
        (new Profile('feature_hooks'))
        ->with_suite(new Suite('default', ['contexts' => ['Feature_Hook_Attributes_Context']]))
    )
    ->with_profile(
        (new Profile('scenario_hooks'))
        ->with_suite(new Suite('default', ['contexts' => ['Scenario_Hook_Attributes_Context']]))
    )
    ->with_profile(
        (new Profile('suite_hooks'))
        ->with_suite(new Suite('apples', ['contexts' => ['Suite_Hook_Attributes_Context']]))
    )
    ->with_profile(
        (new Profile('step_hooks'))
        ->with_suite(new Suite('default', ['contexts' => ['Step_Hook_Attributes_Context']]))
    );
