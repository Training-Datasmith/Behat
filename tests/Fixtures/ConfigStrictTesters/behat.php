<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('with-strict'))
        ->withTesterOptions((new Tester_Options())
            ->withStrictResultInterpretation()))
    ->withProfile((new Profile('not-strict'))
        ->withTesterOptions((new Tester_Options())
            ->withStrictResultInterpretation(false)));
