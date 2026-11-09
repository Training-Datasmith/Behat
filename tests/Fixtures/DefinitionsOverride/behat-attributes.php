<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('inherit'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Inherit_Attributes'],
        ]))
    )
    ->with_profile(
        (new Profile('both_patterns'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Both_Attributes'],
        ]))
    )
    ->with_profile(
        (new Profile('parent_attribute_child_annotation'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Mixed_Attribute_Parent_Annotation_Child'],
        ]))
    )
    ->with_profile(
        (new Profile('parent_annotation_child_attribute'))
        ->with_suite(new Suite('default', [
            'contexts' => ['Feature_Context_Mixed_Annotation_Parent_Attribute_Child'],
        ]))
    );
