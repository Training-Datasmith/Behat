<?php

use Behat\Hook\After_Suite;

final class After_Suite_Context extends Base_Context
{
    #[After_Suite]
    public static function afterSuiteHook(): void
    {
        self::throwFailure('Error in afterSuite hook');
    }
}
