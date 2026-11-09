<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('absolute_paths'))
        ->with_path_options(print_absolute_paths: true)
    );
