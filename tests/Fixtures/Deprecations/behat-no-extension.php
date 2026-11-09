<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withTesterOptions(
                (new Tester_Options())->withPrintBehatDeprecations()
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations_in_steps.feature')
            )
    );
