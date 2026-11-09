<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Feature_Context implements Context
{
    #[Given('I have :count apple(s)')]
    public function iHaveApples($count)
    {
    }

    #[When('I ate :count apple(s)')]
    public function iAteApples($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    public function iShouldHaveApples($count)
    {
    }
}
