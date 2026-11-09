<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Tester_Options;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_extension(new Extension('deprecation_extension.php'))
            ->with_tester_options(
                (new Tester_Options())->with_fail_on_behat_deprecations()
            )
            ->with_suite(
                (new Suite('default'))
                    ->with_paths('features/deprecations.feature')
            )
    );
