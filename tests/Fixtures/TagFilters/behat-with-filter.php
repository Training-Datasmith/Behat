<?php

use Behat\Config\Config;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->with_suite(new Suite('default'))
    ->with_filter(new Tag_Filter('~@slow'))
;

return (new Config())->with_profile($profile);
