<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Hook\Before_Scenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Feature_Context implements Context
{
    private $value;

    #[Given('/I have entered (\d+)/')]
    public function iHaveEntered($num)
    {
        $this->value = $num;
    }

    #[When('/I add (\d+)/')]
    public function iAdd($num)
    {
        $this->value += $num;
    }

    #[When('/^Something not done yet$/')]
    public function somethingNotDoneYet()
    {
        throw new Pending_Exception();
    }

    #[Then('/I must have (\d+)/')]
    public function iMustHave($num)
    {
        PHPUnit\Framework\Assert::assertEquals($num, $this->value);
    }

    #[Before_Scenario('@setup-error')]
    public function setup()
    {
        throw new Exception('This scenario has a failed setup');
    }

    #[When('/^I have a PHP error$/')]
    public function iHaveAPHPError()
    {
        $foo = new class extends Context {};
    }
}
