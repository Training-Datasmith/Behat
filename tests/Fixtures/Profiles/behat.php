<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->import('pretty.php')
    ->with_profile(new Profile('default', [
        'formatters' => [
            'pretty' => false,
            'progress' => null,
        ],
    ]))
    ->with_profile(new Profile('pretty_without_paths', [
        'formatters' => [
            'progress' => false,
            'pretty' => [
                'paths' => false,
            ],
        ],
    ]))
;
