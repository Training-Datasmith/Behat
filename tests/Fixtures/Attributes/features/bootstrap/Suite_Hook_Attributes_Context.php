<?php

use Behat\Hook\After_Suite;
use Behat\Hook\Before_Suite;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Suite_Hook_Attributes_Context implements Behat\Behat\Context\Context
{
    #[Before_Suite]
    public static function beforeSuite()
    {
        echo '= BEFORE SUITE =';
    }

    #[Before_Suite('apples')]
    public static function beforeSuiteApples()
    {
        echo '= BEFORE APPLES =';
    }

    #[After_Suite]
    public static function afterSuite()
    {
        echo '= AFTER SUITE =';
    }

    #[After_Suite('apples')]
    public static function afterSuiteApples()
    {
        echo '= AFTER APPLES =';
    }

    #[Given('I have :count apple(s)')]
    public function iHaveFruit($count)
    {
    }

    #[When('I eat :count apple(s)')]
    public function iEatFruit($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    public function iShouldHaveFruit($count)
    {
    }
}
