<?php

declare(strict_types=1);

use Behat\Hook\BeforeSuite;

final class BeforeSuiteContext extends BaseContext
{
    #[BeforeSuite]
    public static function beforeSuiteHook(): void
    {
        self::throwFailure('Error in beforeSuite hook');
    }
}
