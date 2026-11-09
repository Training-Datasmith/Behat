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
                    'typehinted_service' => [
                        'class' => 'stdClass',
                    ],
                ],
            ]))
            ->add_context('First_Context_Mixed_Args', ['@typehinted_service', 'bar'])
        )
    );
