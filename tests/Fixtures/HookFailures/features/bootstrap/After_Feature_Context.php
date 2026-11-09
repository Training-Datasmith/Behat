<?php

use Behat\Hook\After_Feature;

final class After_Feature_Context extends Base_Context
{
    #[After_Feature]
    public static function afterFeatureHook(): void
    {
        self::throwFailure('Error in afterFeature hook');
    }
}
