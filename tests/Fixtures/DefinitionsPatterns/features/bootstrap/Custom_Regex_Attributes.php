<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class Custom_Regex_Attributes implements Context
{
    #[Given('/^I have entered (\\d+)$/')]
    public function iHaveEntered($number): void
    {
    }
}
