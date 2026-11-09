<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\When;

final class Feature_Context implements Context
{
    #[When('this scenario executes')]
    public function thisScenarioExecutes(): void
    {
    }
}
