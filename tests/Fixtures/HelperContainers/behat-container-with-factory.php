<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('default', [
                'services' => [
                    'shared_service' => [
                        'class' => 'Shared_Service_With_Factory',
                        'factory_method' => 'factoryMethod',
                    ],
                ],
            ]))
            ->addContext('First_Context', ['@shared_service'])
            ->addContext('Second_Context', ['@shared_service'])
        )
    );
