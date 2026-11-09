<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension('deprecation_extension.php'))
            ->withTesterOptions(
                (new Tester_Options())->withFailOnBehatDeprecations()
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations.feature')
            )
    );
