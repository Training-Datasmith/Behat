<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Basic_Context')
    )
;

$definitions = (new Profile('definitions'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Definitions_Context')
    )
;

$translatable = (new Profile('translatable'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Translatable_Definitions_Context')
    )
;

$descriptions = (new Profile('descriptions'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Descriptions_Context')
    )
;

return (new Config())
    ->with_profile($profile)
    ->with_profile($definitions)
    ->with_profile($translatable)
    ->with_profile($descriptions)
;
