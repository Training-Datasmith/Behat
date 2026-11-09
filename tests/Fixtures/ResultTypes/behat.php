<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('undefined'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Undefined_Context')
        )
    )
    ->with_profile(
        (new Profile('pending'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Pending_Context')
        )
    )
    ->with_profile(
        (new Profile('failed'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Failed_Context')
        )
    )
    ->with_profile(
        (new Profile('skipped'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Skipped_Context')
        )
    )
    ->with_profile(
        (new Profile('ambiguous'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Ambiguous_Context')
        )
    )
    ->with_profile(
        (new Profile('redundant'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Redundant_Context')
        )
    )
    ->with_profile(
        (new Profile('warning'))
        ->with_suite(
            (new Suite('default'))
            ->with_contexts('Warning_Context')
        )
    )
;
