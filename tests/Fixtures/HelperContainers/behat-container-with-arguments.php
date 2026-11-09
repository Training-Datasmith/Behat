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
                    'shared_service' => [
                        'class' => 'Shared_Service_Expecting1',
                        'arguments' => [1],
                    ],
                ],
            ]))
            ->addContext('First_Context', ['@shared_service'])
            ->addContext('Second_Context', ['@shared_service'])
        )
    );
