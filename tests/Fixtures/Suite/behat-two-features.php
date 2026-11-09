<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$firstSuite = (new Suite('first'))
  ->with_paths('%paths.base%/features/first')
  ->with_contexts('First_Context')
;

$secondSuite = (new Suite('second'))
  ->with_paths('%paths.base%/features/second')
  ->with_contexts('Second_Context')
;

$profile = (new Profile('default'))
  ->with_suite($firstSuite)
  ->with_suite($secondSuite)
;

return (new Config())->with_profile($profile);
