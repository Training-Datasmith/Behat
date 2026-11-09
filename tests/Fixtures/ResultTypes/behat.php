<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile(
        (new Profile('undefined'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Undefined_Context')
        )
    )
    ->withProfile(
        (new Profile('pending'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Pending_Context')
        )
    )
    ->withProfile(
        (new Profile('failed'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Failed_Context')
        )
    )
    ->withProfile(
        (new Profile('skipped'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Skipped_Context')
        )
    )
    ->withProfile(
        (new Profile('ambiguous'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Ambiguous_Context')
        )
    )
    ->withProfile(
        (new Profile('redundant'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Redundant_Context')
        )
    )
    ->withProfile(
        (new Profile('warning'))
        ->withSuite(
            (new Suite('default'))
            ->withContexts('Warning_Context')
        )
    )
;
