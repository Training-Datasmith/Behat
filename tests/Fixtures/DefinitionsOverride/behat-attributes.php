<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('inherit'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Feature_Context_Inherit_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('both_patterns'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Feature_Context_Both_Attributes'],
        ]))
    )
    ->withProfile(
        (new Profile('parent_attribute_child_annotation'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Feature_Context_Mixed_Attribute_Parent_Annotation_Child'],
        ]))
    )
    ->withProfile(
        (new Profile('parent_annotation_child_attribute'))
        ->withSuite(new Suite('default', [
            'contexts' => ['Feature_Context_Mixed_Annotation_Parent_Attribute_Child'],
        ]))
    );
