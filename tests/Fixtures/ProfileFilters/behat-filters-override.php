<?php

use Behat\Config\Config;
use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_gherkin_options(
                (new Gherkin_Options())
                ->with_filter(new Tag_Filter('~@wip'))
            )
    )
    ->with_profile(
        (new Profile('wip'))
            ->with_gherkin_options(
                (new Gherkin_Options())
                ->with_filter(new Name_Filter('A simple feature'))
            )
    )
;
