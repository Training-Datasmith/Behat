<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Descriptions_Context implements Context
{
    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new Pending_Exception();
    }

    /**
     * Eating apples.
     *
     * More details on eating apples, and a list:
     * - one
     * - two
     * --
     * Internal note not showing in help.
     */
    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new Pending_Exception();
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new Pending_Exception();
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new Pending_Exception();
    }
}
