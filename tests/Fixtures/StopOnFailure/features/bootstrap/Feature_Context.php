<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;

class Feature_Context implements Context
{
    #[Given('/^I have (?:a|another) step that passes?$/')]
    #[Then('/^I should have a scenario that passed$/')]
    public function passing()
    {
    }

    #[Given('/^I have (?:a|another) step that fails?$/')]
    #[Then('/^I should have a scenario that failed$/')]
    public function failing()
    {
        throw new Exception('step failed as supposed');
    }
}
