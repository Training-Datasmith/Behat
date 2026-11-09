<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('remove_prefix'))
        ->with_path_options(remove_prefix: [
            'features' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR,
            'features' . DIRECTORY_SEPARATOR,
        ])
    );
