<?php

use Behat\Config\Config;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withGherkinOptions(
                (new Gherkin_Options())
                ->withFilter(new Tag_Filter('tag2'))
            )
    )
;
