<?php

use Behat\Config\Config;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(
        (new Profile('default'))
            ->with_filter(new Tag_Filter('tag2'))
    )
;
