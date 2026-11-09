<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('annotations'))
        ->with_suite(
            (new Suite('xliff'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Feature_Context_Annotations')
        )
        ->with_suite(
            (new Suite('yaml'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Yaml_Context_Annotations')
        )
        ->with_suite(
            (new Suite('php'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Php_Context_Annotations')
        )
        ->with_suite(
            (new Suite('arguments'))
            ->with_paths('features/calc_ru_arguments.feature')
            ->with_contexts('Arguments_Context_Annotations')
        )
    )
    ->with_profile(
        (new Profile('attributes'))
        ->with_suite(
            (new Suite('xliff'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Feature_Context_Attributes')
        )
        ->with_suite(
            (new Suite('yaml'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Yaml_Context_Attributes')
        )
        ->with_suite(
            (new Suite('php'))
            ->with_paths('features/calc_ru.feature')
            ->with_contexts('Php_Context_Attributes')
        )
        ->with_suite(
            (new Suite('arguments'))
            ->with_paths('features/calc_ru_arguments.feature')
            ->with_contexts('Arguments_Context_Attributes')
        )
    )
;
