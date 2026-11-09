<?php

declare(strict_types=1);

use Behat\Hook\After_Scenario;
use Behat\Hook\Before_Scenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Scenario_Hook_Attributes_Context implements Behat\Behat\Context\Context
{
    #[Before_Scenario]
    public function beforeScenario()
    {
        echo '= BEFORE SCENARIO =';
    }

    #[Before_Scenario('@bananas')]
    public function beforeBananas()
    {
        echo '= BEFORE BANANAS =';
    }

    #[After_Scenario]
    public function afterScenario()
    {
        echo '= AFTER SCENARIO =';
    }

    #[After_Scenario('@bananas')]
    public function afterBananas()
    {
        echo '= AFTER BANANAS =';
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
