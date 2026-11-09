<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Definitions_Context implements Context
{
    #[Given('/^(?:I|We) have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new Pending_Exception();
    }

    #[When('/^(?:I|We) ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new Pending_Exception();
    }

    #[When('/^(?:I|We) found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new Pending_Exception();
    }

    #[Then('/^(?:I|We) should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new Pending_Exception();
    }
}
