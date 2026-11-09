<?php

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

final class Feature_Context implements Context
{
    #[Before_Scenario]
    public static function beforeScenario(): void
    {
        echo 'HOOK: before scenario';
    }

    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples(int $count): void
    {
        echo "STEP: I have $count apples";
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples(int $count): void
    {
        echo "STEP: I ate $count apples";
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples(int $count): void
    {
        echo "STEP: I found $count apples";
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples(int $count): void
    {
        echo "STEP: I should have $count apples";
    }
}
