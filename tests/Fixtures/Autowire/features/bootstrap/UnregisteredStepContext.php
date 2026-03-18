<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class UnregisteredStepContext implements Context
{
    #[Given('a step')]
    public function aStep(Service4 $s)
    {
    }
}
