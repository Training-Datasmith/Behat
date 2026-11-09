<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
  ->with_suite(
      (new Suite('first', ['contexts' => ['First_Context']]))
        ->with_paths('%paths.base%/features/some.feature')
  )
  ->with_suite(
      (new Suite('second'))
        ->with_contexts('Second_Context')
        ->with_paths('%paths.base%/features/some.feature')
  )
;

return (new Config())->with_profile($profile);
