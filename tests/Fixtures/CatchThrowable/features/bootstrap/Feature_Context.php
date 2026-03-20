<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    #[When('/^I have some code with a fatal error$/')]
    public function iHaveSomeCodeWithFatalError()
    {
        'not an object'->method();
    }

    #[Then('/^I should be skipped$/')]
    public function iShouldBeSkipped()
    {
    }
}
