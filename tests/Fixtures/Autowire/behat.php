<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('constructor_args'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('mixed_constructor_args'))
        ->with_suite((new Suite('default', [
            'autowire' => true,
            'services' => 'Service_Container',
        ]))->add_context('Mixed_Constructor_Args_Context', [
            'name' => 'Konstantin',
            's2' => '@Service2',
        ]))
    )
    ->with_profile(
        (new Profile('null_args'))
        ->with_suite((new Suite('default', [
            'autowire' => true,
            'services' => 'Service_Container',
        ]))->add_context('Null_Args_Context', [
            'name' => null,
        ]))
    )
    ->with_profile(
        (new Profile('unregistered_constructor'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Unregistered_Constructor_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('step_definition_args'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Step_Definition_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('unregistered_step'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Unregistered_Step_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('transformation_args'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Transformation_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->with_profile(
        (new Profile('union_constructor_args'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Union_Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    );
