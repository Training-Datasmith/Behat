<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Testwork\Deprecation\Deprecation_Collector;

class Feature_Context implements Context
{
    #[Before_Scenario('@deprecation-in-hook')]
    public function beforeScenario(): void
    {
        Deprecation_Collector::trigger('Deprecation triggered in hook');
    }

    #[Given('I run a step')]
    public function iRunAStep(): void
    {
    }

    #[Given('I run a step that triggers a deprecation')]
    public function iRunAStepThatTriggersADeprecation(): void
    {
        Deprecation_Collector::trigger('Deprecation triggered in step definition');
    }

    #[Given('I run a step that triggers an unsuppressed deprecation')]
    public function iRunAStepThatTriggersAnUnsuppressedDeprecation(): void
    {
        trigger_error('Deprecation triggered in step definition', E_USER_DEPRECATED);
    }

    #[Then('the step should pass')]
    public function theStepShouldPass(): void
    {
    }
}
