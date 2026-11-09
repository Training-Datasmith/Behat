<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        new Profile('default')
    );
