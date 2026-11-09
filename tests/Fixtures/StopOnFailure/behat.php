<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('stop-on-failure'))
        ->withTesterOptions((new Tester_Options())
            ->withStopOnFailure()))
    ->withProfile((new Profile('no-stop-on-failure'))
        ->withTesterOptions((new Tester_Options())
            ->withStopOnFailure(false)));
