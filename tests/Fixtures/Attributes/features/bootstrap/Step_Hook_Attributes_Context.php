<?php

use Behat\Hook\After_Step;
use Behat\Hook\Before_Step;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Step_Hook_Attributes_Context implements Behat\Behat\Context\Context
{
    #[Before_Step]
    public function beforeStep()
    {
        echo '= BEFORE STEP =';
    }

    #[After_Step]
    public function afterStep()
    {
        echo '= AFTER STEP =';
    }

    #[Before_Step('I have 3 apples')]
    public function beforeApples()
    {
        echo '= BEFORE APPLES =';
    }

    #[After_Step('I have 3 apples')]
    public function afterApples()
    {
        echo '= AFTER APPLES =';
    }

    #[Given('I have :count apple(s)')]
    #[Given('I have :count banana(s)')]
    public function iHaveFruit($count)
    {
    }

    #[When('I eat :count apple(s)')]
    #[When('I eat :count banana(s)')]
    public function iEatFruit($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    #[Then('I should have :count banana(s)')]
    public function iShouldHaveFruit($count)
    {
    }
}
