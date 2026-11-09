<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('default', [
                'services' => [
                    'parent_class' => [
                        'class' => 'Parent_Class',
                    ],
                    'child_class' => [
                        'class' => 'Child_Class',
                    ],
                ],
            ]))
            ->addContext('First_Context_Typehinted', ['@child_class', '@parent_class'])
        )
    );
