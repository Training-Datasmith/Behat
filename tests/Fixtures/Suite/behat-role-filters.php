<?php

use Behat\Config\Config;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
  ->with_suite(
      (new Suite('little_kid'))
        ->with_contexts(Little_Kid_Context::class)
        ->with_paths('%paths.base%/features/little_kid.feature')
        ->with_filter(new Role_Filter('little kid'))
  )
  ->with_suite(
      (new Suite('big_brother'))
        ->with_contexts(Big_Brother_Context::class)
        ->with_paths('%paths.base%/features/big_brother.feature')
        ->with_filter(new Role_Filter('big brother'))
  )
;

return (new Config())->with_profile($profile);
