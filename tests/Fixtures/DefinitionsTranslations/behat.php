<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('annotations'))
        ->withSuite(
            (new Suite('xliff'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Feature_Context_Annotations')
        )
        ->withSuite(
            (new Suite('yaml'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Yaml_Context_Annotations')
        )
        ->withSuite(
            (new Suite('php'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Php_Context_Annotations')
        )
        ->withSuite(
            (new Suite('arguments'))
            ->withPaths('features/calc_ru_arguments.feature')
            ->withContexts('Arguments_Context_Annotations')
        )
    )
    ->withProfile(
        (new Profile('attributes'))
        ->withSuite(
            (new Suite('xliff'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Feature_Context_Attributes')
        )
        ->withSuite(
            (new Suite('yaml'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Yaml_Context_Attributes')
        )
        ->withSuite(
            (new Suite('php'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('Php_Context_Attributes')
        )
        ->withSuite(
            (new Suite('arguments'))
            ->withPaths('features/calc_ru_arguments.feature')
            ->withContexts('Arguments_Context_Attributes')
        )
    )
;
