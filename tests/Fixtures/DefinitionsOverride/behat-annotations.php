<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('inherit'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Inherit_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('both_patterns'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Both_Annotations'],
        ]))
    );
