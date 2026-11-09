<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Basic_Context')
    )
;

$definitions = (new Profile('definitions'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Definitions_Context')
    )
;

$translatable = (new Profile('translatable'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Translatable_Definitions_Context')
    )
;

$descriptions = (new Profile('descriptions'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Descriptions_Context')
    )
;

return (new Config())
    ->withProfile($profile)
    ->withProfile($definitions)
    ->withProfile($translatable)
    ->withProfile($descriptions)
;
