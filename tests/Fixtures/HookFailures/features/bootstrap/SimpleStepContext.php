<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\When;

final class SimpleStepContext implements Context
{
    #[When('I have a simple step')]
    public function iHaveASimpleStep(): void
    {
    }
}
