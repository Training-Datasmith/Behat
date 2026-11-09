<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_extension(new Extension('custom_extension.php'))
    );
