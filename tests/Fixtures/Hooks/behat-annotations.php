<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())->withProfile(
    (new Profile('default'))->withSuite(
        new Suite('default', [
            'contexts' => ['Feature_Context_Annotations'],
            'parameters' => [
                'before_feature' => 'BEFORE EVERY FEATURE',
                'after_feature' => 'AFTER EVERY FEATURE',
            ],
        ])
    )
);
