<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withTesterOptions(
                (new Tester_Options())->withErrorReporting(E_USER_DEPRECATED)
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/unsuppressed_deprecation.feature')
            )
    );
