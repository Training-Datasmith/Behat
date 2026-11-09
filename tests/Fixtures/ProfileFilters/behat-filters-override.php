<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withGherkinOptions(
                (new Gherkin_Options())
                ->withFilter(new Tag_Filter('~@wip'))
            )
    )
    ->withProfile(
        (new Profile('wip'))
            ->withGherkinOptions(
                (new Gherkin_Options())
                ->withFilter(new Name_Filter('A simple feature'))
            )
    )
;
