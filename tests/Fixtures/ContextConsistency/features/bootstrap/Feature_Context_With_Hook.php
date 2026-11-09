<?php

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Behat\Step\Given;

class Feature_Context_With_Hook implements Context
{
    #[Before_Scenario]
    public function beforeScenario(): void
    {
        echo 'Setting up';
    }

    #[Given('step')]
    public function step(): void
    {
    }
}
