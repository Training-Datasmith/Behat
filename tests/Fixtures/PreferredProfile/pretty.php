<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

$profile = (new Profile('pretty_without_paths'))
    ->disable_formatter(Progress_Formatter::NAME)
    ->with_formatter(new Pretty_Formatter(paths: false))
;

return (new Config())->with_profile($profile);
