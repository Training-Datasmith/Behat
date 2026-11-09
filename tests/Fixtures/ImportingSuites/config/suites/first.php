<?php

$config = new Behat\Config\Config([
    'default' => [
        'suites' => [
            'first' => [
                'contexts' => ['First_Context'],
            ],
        ],
    ],
]);

return $config;
