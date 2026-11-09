<?php

use Behat\Hook\Before_Step;

final class Before_Step_Context extends Base_Context
{
    #[Before_Step]
    public function beforeStepHook(): void
    {
        self::throwFailure('Error in beforeStep hook');
    }
}
