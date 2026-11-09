<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context')
    )
;

$multilineProfile = (new Profile('multiline'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context_Multiline')
    )
;

$emptyProfile = (new Profile('empty'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context_Empty')
    )
;

$backgroundFailingProfile = (new Profile('background_failing'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context_Background_Failing')
    )
;

$multipleExamplesProfile = (new Profile('multiple_examples'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context_Multiple_Examples')
    )
;

$lsProfile = (new Profile('ls'))
    ->with_suite(
        (new Suite('default'))
            ->with_contexts('Feature_Context_Ls')
    )
;

return (new Config())
    ->with_profile($defaultProfile)
    ->with_profile($multilineProfile)
    ->with_profile($emptyProfile)
    ->with_profile($backgroundFailingProfile)
    ->with_profile($multipleExamplesProfile)
    ->with_profile($lsProfile)
;
