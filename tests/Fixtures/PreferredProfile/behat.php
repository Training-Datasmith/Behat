<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

$defaultProfile = new Profile('default');

$progressProfile = (new Profile('progress'))
    ->disableFormatter(Pretty_Formatter::NAME)
    ->withFormatter(new Progress_Formatter())
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($progressProfile)
    ->withPreferredProfile('progress')
    ->import('pretty.php')
;
