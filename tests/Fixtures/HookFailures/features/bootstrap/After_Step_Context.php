<?php

use Behat\Hook\After_Step;

final class After_Step_Context extends Base_Context
{
    #[After_Step]
    public function afterStepHook(): void
    {
        self::throwFailure('Error in afterStep hook');
    }
}
