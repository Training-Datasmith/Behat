<?php

declare(strict_types=1);

use Behat\Hook\After_Scenario;

final class After_Scenario_Context extends Base_Context
{
    #[After_Scenario]
    public function afterScenarioHook(): void
    {
        self::throwFailure('Error in afterScenario hook');
    }
}
