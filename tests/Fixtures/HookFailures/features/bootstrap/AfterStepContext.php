<?php

declare(strict_types=1);

use Behat\Hook\AfterStep;

final class AfterStepContext extends BaseContext
{
    #[AfterStep]
    public function afterStepHook(): void
    {
        self::throwFailure('Error in afterStep hook');
    }
}
