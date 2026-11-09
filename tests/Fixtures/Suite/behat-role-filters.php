<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
  ->withSuite(
      (new Suite('little_kid'))
        ->withContexts(Little_Kid_Context::class)
        ->withPaths('%paths.base%/features/little_kid.feature')
        ->withFilter(new Role_Filter('little kid'))
  )
  ->withSuite(
      (new Suite('big_brother'))
        ->withContexts(Big_Brother_Context::class)
        ->withPaths('%paths.base%/features/big_brother.feature')
        ->withFilter(new Role_Filter('big brother'))
  )
;

return (new Config())->withProfile($profile);
