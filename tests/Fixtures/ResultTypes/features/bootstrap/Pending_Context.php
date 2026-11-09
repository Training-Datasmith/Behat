<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\When;

class Pending_Context implements Context
{
    #[Given('/^human have ordered very very very hot "([^"]*)"$/')]
    public function humanOrdered($arg1): void
    {
        throw new Pending_Exception();
    }

    #[When('the coffee will be ready')]
    public function theCoffeeWillBeReady(): void
    {
        throw new Pending_Exception();
    }
}
