<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(
                new Extension(Translator_Extension::class, [
                    'locale' => 'fr',
                ])
            )
    );
