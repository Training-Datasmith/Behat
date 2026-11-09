<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('token_start'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Token_Start_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('decimal_point'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Decimal_Point_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('string_with_point'))
        ->with_suite(new Suite('default', [
            'contexts' => ['String_With_Point_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('broken_regex'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Broken_Regex_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('custom_regex'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Custom_Regex_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('decimal_number'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Decimal_Number_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('empty_parameter'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Empty_Parameter_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('unix_path'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Unix_Path_Annotations'],
        ]))
    )
    ->with_profile(
        (new Profile('negative_number'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Negative_Number_Annotations'],
        ]))
    );
