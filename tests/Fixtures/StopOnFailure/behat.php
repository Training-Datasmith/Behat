<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile((new Profile('stop-on-failure'))
        ->with_tester_options((new Tester_Options())
            ->with_stop_on_failure()))
    ->with_profile((new Profile('no-stop-on-failure'))
        ->with_tester_options((new Tester_Options())
            ->with_stop_on_failure(false)));
