<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('constructor_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('mixed_constructor_args'))
        ->withSuite((new Suite('default', [
            'autowire' => true,
            'services' => 'Service_Container',
        ]))->addContext('Mixed_Constructor_Args_Context', [
            'name' => 'Konstantin',
            's2' => '@Service2',
        ]))
    )
    ->withProfile(
        (new Profile('null_args'))
        ->withSuite((new Suite('default', [
            'autowire' => true,
            'services' => 'Service_Container',
        ]))->addContext('Null_Args_Context', [
            'name' => null,
        ]))
    )
    ->withProfile(
        (new Profile('unregistered_constructor'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Unregistered_Constructor_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('step_definition_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Step_Definition_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('unregistered_step'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Unregistered_Step_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('transformation_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Transformation_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    )
    ->withProfile(
        (new Profile('union_constructor_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Union_Constructor_Args_Context'],
            'autowire' => true,
            'services' => 'Service_Container',
        ]))
    );
