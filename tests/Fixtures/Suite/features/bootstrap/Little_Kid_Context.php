<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Little_Kid_Context implements Context
{
    #[Given('I have :count apple(s)')]
    public function iHaveApples($count): void
    {
    }

    #[When('I ate :count apple(s)')]
    public function iAteApples($count): void
    {
    }

    #[Then('I should have :count apple(s)')]
    public function iShouldHaveApples($count): void
    {
    }
}
