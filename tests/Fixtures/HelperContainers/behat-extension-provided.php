<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_extension(new Extension('My_Extension.php'))
        ->with_suite(
            (new Suite('default', ['services' => '@my_extension.container']))
            ->add_context('First_Context', ['@shared_service'])
            ->add_context('Second_Context', ['@shared_service'])
        )
    );
