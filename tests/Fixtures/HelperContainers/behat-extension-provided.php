<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withExtension(new Extension('My_Extension.php'))
        ->withSuite(
            (new Suite('default', ['services' => '@my_extension.container']))
            ->addContext('First_Context', ['@shared_service'])
            ->addContext('Second_Context', ['@shared_service'])
        )
    );
