<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Gherkin_Options;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withGherkinOptions(
                (new Gherkin_Options())
                ->withFilter(new Role_Filter('second user'))
            )
    )
;
