<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        new Profile('pretty', [
            'formatters' => [
                'progress' => false,
                'pretty' => null,
            ],
        ])
    )
;
