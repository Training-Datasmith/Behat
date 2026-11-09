<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context'],
        ]))
    )
    ->with_profile(
        (new Profile('params'))
        ->with_suite((new Suite('default', []))->add_context('Feature_Context', [
            'parameter1' => 'val_one',
            'parameter2' => [
                'everzet' => 'behat_admin',
                'avalanche123' => 'behat_admin',
            ],
        ]))
    )
    ->with_profile(
        (new Profile('params_optional'))
        ->with_suite((new Suite('default', []))->add_context('Feature_Context', [
            'parameter1' => 'val_one',
        ]))
    )
    ->with_profile(
        (new Profile('custom_context'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Custom_Context'],
        ]))
    )
    ->with_profile(
        (new Profile('array_arguments'))
        ->with_suite((new Suite('default', []))->add_context('First_Context', [
            ['foo', 'bar'],
        ]))
    )
    ->with_profile(
        (new Profile('empty_contexts'))
        ->with_suite(new Suite('first', [
            'contexts' => [],
        ]))
    )
    ->with_profile(
        (new Profile('custom_context_with_hook'))
        ->with_suite(new Suite('first', [
            'contexts' => ['Custom_Context_With_Hook'],
        ]))
    )
    ->with_profile(
        (new Profile('single_context'))
        ->with_suite(new Suite('default', [
            'contexts' => 'UnexistentContext',
        ]))
    )
    ->with_profile(
        (new Profile('unexisting_context'))
        ->with_suite(new Suite('default', [
            'contexts' => ['UnexistentContext'],
        ]))
    )
    ->with_profile(
        (new Profile('unexisting_param'))
        ->with_suite((new Suite('default', []))->add_context('Feature_Context', [
            'unexistingParam' => 'value',
        ]))
    );
