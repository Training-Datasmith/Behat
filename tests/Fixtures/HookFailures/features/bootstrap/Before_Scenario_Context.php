<?php

use Behat\Hook\Before_Scenario;

final class Before_Scenario_Context extends Base_Context
{
    #[Before_Scenario]
    public function beforeScenarioHook(): void
    {
        self::throwFailure('Error in beforeScenario hook');
    }
}
