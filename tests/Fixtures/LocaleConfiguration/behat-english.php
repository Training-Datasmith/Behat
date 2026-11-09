<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_extension(
                new Extension(Translator_Extension::class, [
                    'locale' => 'en',
                ])
            )
    );
