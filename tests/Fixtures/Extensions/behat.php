<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_extension(new Extension('custom_extension.php', [
            'param1' => 'val1',
            'param2' => 'val2',
        ]))
        ->with_suite(new Suite('default', ['contexts' => ['Feature_Context']]))
    );
