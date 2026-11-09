<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->with_suite(new Suite('default'))
;

return (new Config())->with_profile($profile);
