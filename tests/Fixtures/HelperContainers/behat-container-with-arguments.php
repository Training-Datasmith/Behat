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
                    'shared_service' => [
                        'class' => 'Shared_Service_Expecting1',
                        'arguments' => [1],
                    ],
                ],
            ]))
            ->add_context('First_Context', ['@shared_service'])
            ->add_context('Second_Context', ['@shared_service'])
        )
    );
