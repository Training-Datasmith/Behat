<?php

$config = new Behat\Config\Config([
    'default' => [
        'suites' => [
            'second' => [
                'contexts' => ['Second_Context'],
            ],
        ],
    ],
]);

return $config;
