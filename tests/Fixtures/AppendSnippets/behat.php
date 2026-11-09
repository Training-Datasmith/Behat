<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile(
        (new Profile('no_pending_exception'))
        ->withSuite(new Suite('default', ['contexts' => ['Feature_Context_No_Pending_Exception']]))
    )
    ->withProfile(
        (new Profile('minimal_imports'))
        ->withSuite(new Suite('default', ['contexts' => ['Feature_Context_Minimal_Imports']]))
    )
    ->withProfile(
        (new Profile('multicontext'))
        ->withSuite(new Suite('first', ['contexts' => ['First_Context']]))
        ->withSuite(new Suite('second', ['contexts' => ['Second_Context']]))
    );
