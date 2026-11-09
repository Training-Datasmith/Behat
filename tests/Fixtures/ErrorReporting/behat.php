<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile((new Profile('do-not-ignore-any'))
        ->with_tester_options((new Tester_Options())
            ->with_error_reporting(E_ALL)))
    ->with_profile((new Profile('ignore-all-but-error'))
        ->with_tester_options((new Tester_Options())
            ->with_error_reporting(E_ALL & ~(E_WARNING | E_NOTICE | E_DEPRECATED))))
;
