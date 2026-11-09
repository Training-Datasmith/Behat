<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$firstSuite = (new Suite('first'))
  ->withPaths('%paths.base%/features/first')
  ->withContexts('First_Context')
;

$secondSuite = (new Suite('second'))
  ->withPaths('%paths.base%/features/second')
  ->withContexts('Second_Context')
;

$profile = (new Profile('default'))
  ->withSuite($firstSuite)
  ->withSuite($secondSuite)
;

return (new Config())->withProfile($profile);
