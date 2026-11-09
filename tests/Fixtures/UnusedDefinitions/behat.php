<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(
            (new Suite('first_suite'))
            ->with_paths(
                'features/first_feature.feature'
            )
        )
        ->with_suite(
            (new Suite('second_suite'))
            ->with_paths(
                'features/second_feature.feature',
            )
        )
    )
    ->with_profile(
        (new Profile('translated_definitions'))
            ->with_suite(
                (new Suite('translated_definitions'))
                    ->with_paths(
                        'features/translated_definitions.feature'
                    )
                    ->with_contexts(
                        'Translated_Definitions_Context',
                    )
            )
    )
    ->with_profile(
        (new Profile('unused_definitions'))
        ->with_print_unused_definitions()
    )
;
