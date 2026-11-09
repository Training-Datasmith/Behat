<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

final class Second_Context implements Context
{
    #[Given('I have :count apple(s)')]
    public function iHaveApples(int $count): void
    {
    }

    #[When('I ate :count apple(s)')]
    public function iAteApples(int $count): void
    {
    }

    #[Then('I should have :count apple(s)')]
    public function iShouldHaveApples(int $count): void
    {
    }
}
