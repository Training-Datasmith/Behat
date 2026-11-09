<?php

use Behat\Config\Config;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_formatter(new Progress_Formatter())
    );
