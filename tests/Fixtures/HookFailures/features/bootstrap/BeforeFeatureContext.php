<?php

declare(strict_types=1);

use Behat\Hook\BeforeFeature;

final class BeforeFeatureContext extends BaseContext
{
    #[BeforeFeature]
    public static function beforeFeatureHook(): void
    {
        self::throwFailure('Error in beforeFeature hook');
    }
}
