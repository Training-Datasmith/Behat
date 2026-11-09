<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Tester_Options;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('do-not-ignore-any'))
        ->withTesterOptions((new Tester_Options())
            ->withErrorReporting(E_ALL)))
    ->withProfile((new Profile('ignore-all-but-error'))
        ->withTesterOptions((new Tester_Options())
            ->withErrorReporting(E_ALL & ~(E_WARNING | E_NOTICE | E_DEPRECATED))))
;
