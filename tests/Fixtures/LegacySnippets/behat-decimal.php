<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('default'))->with_suite(
            (new Suite('default'))
                ->add_context('Feature_Context_Decimal')
        )
    )
;
