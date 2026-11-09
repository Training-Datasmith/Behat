<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(
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
            ->add_context('First_Context_Typehinted', ['@child_class', '@parent_class'])
        )
    );
