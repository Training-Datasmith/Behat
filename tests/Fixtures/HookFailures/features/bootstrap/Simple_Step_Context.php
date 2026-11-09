<?php

use Behat\Behat\Context\Context;
use Behat\Step\When;

final class Simple_Step_Context implements Context
{
    #[When('I have a simple step')]
    public function iHaveASimpleStep(): void
    {
    }
}
