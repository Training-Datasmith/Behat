<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withSuite(new Suite('default'))
;

return (new Config())->withProfile($profile);
