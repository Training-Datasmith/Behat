<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('token_start'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Token_Start_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('decimal_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Decimal_Point_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('string_with_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['String_With_Point_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('broken_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Broken_Regex_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('custom_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Custom_Regex_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('decimal_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Decimal_Number_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('empty_parameter'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Empty_Parameter_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('unix_path'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Unix_Path_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('negative_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Negative_Number_Attributes'],
        ]))
    );
