<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context')
    )
;

return (new Config())
    ->withProfile($defaultProfile)
;
