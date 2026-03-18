<?php

declare(strict_types=1);

use Behat\Hook\AfterSuite;

final class AfterSuiteContext extends BaseContext
{
    #[AfterSuite]
    public static function afterSuiteHook(): void
    {
        self::throwFailure('Error in afterSuite hook');
    }
}
