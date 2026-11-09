<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context')
    )
;

$multilineProfile = (new Profile('multiline'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context_Multiline')
    )
;

$emptyProfile = (new Profile('empty'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context_Empty')
    )
;

$backgroundFailingProfile = (new Profile('background_failing'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context_Background_Failing')
    )
;

$multipleExamplesProfile = (new Profile('multiple_examples'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context_Multiple_Examples')
    )
;

$lsProfile = (new Profile('ls'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('Feature_Context_Ls')
    )
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($multilineProfile)
    ->withProfile($emptyProfile)
    ->withProfile($backgroundFailingProfile)
    ->withProfile($multipleExamplesProfile)
    ->withProfile($lsProfile)
;
