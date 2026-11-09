<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\Then;

class Ambiguous_Context implements Context
{
    #[Given('/^human have chosen "([^"]*)"$/')]
    public function chosen($arg1): void
    {
        throw new Pending_Exception();
    }

    #[Given('/^human have chosen "Latte"$/')]
    public function chosenLatte(): void
    {
        throw new Pending_Exception();
    }

    #[Then('/^I should make him "([^"]*)"$/')]
    public function iShouldSee($money): void
    {
        throw new Pending_Exception();
    }
}
