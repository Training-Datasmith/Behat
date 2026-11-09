<?php

use Behat\Hook\Before_Feature;

final class Before_Feature_Context extends Base_Context
{
    #[Before_Feature]
    public static function beforeFeatureHook(): void
    {
        self::throwFailure('Error in beforeFeature hook');
    }
}
