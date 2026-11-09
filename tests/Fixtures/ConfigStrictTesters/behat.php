<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile((new Profile('with-strict'))
        ->with_tester_options((new Tester_Options())
            ->with_strict_result_interpretation()))
    ->with_profile((new Profile('not-strict'))
        ->with_tester_options((new Tester_Options())
            ->with_strict_result_interpretation(false)));
