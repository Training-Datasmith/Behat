<?php

use Behat\Hook\Before_Suite;

final class Before_Suite_Context extends Base_Context
{
    #[Before_Suite]
    public static function beforeSuiteHook(): void
    {
        self::throwFailure('Error in beforeSuite hook');
    }
}
