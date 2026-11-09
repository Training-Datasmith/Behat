<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

$defaultProfile = new Profile('default');

$progressProfile = (new Profile('progress'))
    ->disable_formatter(Pretty_Formatter::NAME)
    ->with_formatter(new Progress_Formatter())
;

return (new Config())
    ->with_profile($defaultProfile)
    ->with_profile($progressProfile)
    ->with_preferred_profile('progress')
    ->import('pretty.php')
;
