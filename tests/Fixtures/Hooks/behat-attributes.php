<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())->with_profile(
    (new Profile('default'))->with_suite(
        new Suite('default', [
            'contexts' => ['Feature_Context_Attributes'],
            'parameters' => [
                'before_feature' => 'BEFORE EVERY FEATURE',
                'after_feature' => 'AFTER EVERY FEATURE',
            ],
        ])
    )
);
