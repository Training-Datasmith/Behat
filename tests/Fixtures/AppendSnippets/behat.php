<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('no_pending_exception'))
        ->with_suite(new Suite('default', ['contexts' => ['Feature_Context_No_Pending_Exception']]))
    )
    ->with_profile(
        (new Profile('minimal_imports'))
        ->with_suite(new Suite('default', ['contexts' => ['Feature_Context_Minimal_Imports']]))
    )
    ->with_profile(
        (new Profile('multicontext'))
        ->with_suite(new Suite('first', ['contexts' => ['First_Context']]))
        ->with_suite(new Suite('second', ['contexts' => ['Second_Context']]))
    );
