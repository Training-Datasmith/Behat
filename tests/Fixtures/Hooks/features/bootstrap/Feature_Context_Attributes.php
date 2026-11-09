<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\After_Feature;
use Behat\Hook\After_Scenario;
use Behat\Hook\After_Step;
use Behat\Hook\Before_Feature;
use Behat\Hook\Before_Scenario;
use Behat\Hook\Before_Step;
use Behat\Step\Given;
use Behat\Step\Then;

class Feature_Context_Attributes implements Context
{
    private $number;
    private $scenarioFilter;

    #[Before_Feature]
    public static function doSomethingBeforeFeature($event)
    {
        $params = $event->get_suite()->getSetting('parameters');
        echo '= do something ' . $params['before_feature'];
    }

    #[After_Feature]
    public static function doSomethingAfterFeature($event)
    {
        $params = $event->get_suite()->getSetting('parameters');
        echo '= do something ' . $params['after_feature'];
    }

    #[Before_Feature('@someFeature')]
    public static function doSomethingBeforeSomeFeature($event)
    {
        echo '= do something before SOME feature';
    }

    #[After_Feature('@someFeature')]
    public static function doSomethingAfterSomeFeature($event)
    {
        echo '= do something after SOME feature';
    }

    #[Before_Scenario]
    public function beforeScenario($event)
    {
        $this->number = 50;
    }

    #[Before_Scenario('@thirty')]
    public function beforeScenarioThirty($event)
    {
        $this->number = 30;
    }

    #[Before_Scenario('@exception')]
    public function beforeScenarioException($event)
    {
        throw new Exception('Exception');
    }

    #[Before_Scenario('@filtered')]
    public function beforeScenarioFiltered($event)
    {
        $this->scenarioFilter = 'filtered';
    }

    #[Before_Scenario('@~filtered')]
    public function beforeScenarioNotFiltered($event)
    {
        $this->scenarioFilter = '~filtered';
    }

    #[Before_Step('I must have 100')]
    public function beforeStep100($event)
    {
        $this->number = 100;
    }

    #[Given('/^I have entered (\\d+)$/')]
    public function iHaveEntered($number)
    {
        $this->number = intval($number);
    }

    #[Then('/^I must have (\\d+)$/')]
    public function iMustHave($number)
    {
        PHPUnit\Framework\Assert::assertEquals(intval($number), $this->number);
    }

    #[Then('I must have a scenario filter value of :value')]
    public function iMustHaveScenarioFilter($filterValue)
    {
        PHPUnit\Framework\Assert::assertEquals($filterValue, $this->scenarioFilter);
    }

    #[Before_Scenario]
    public function passingBeforeScenarioHook()
    {
        echo 'Is passing';
    }

    #[After_Scenario]
    public function passingAfterScenarioHook()
    {
        echo 'Is passing';
    }

    #[Before_Scenario('@failing-before-hook')]
    public function failingBeforeScenarioHook()
    {
        throw new RuntimeException('Is failing');
    }

    #[Before_Step]
    public function passingBeforeStep()
    {
        echo 'Is passing';
    }

    #[Before_Step('passing step with failing hook')]
    public function failingBeforeStep()
    {
        throw new RuntimeException('Is failing');
    }

    #[After_Step]
    public function passingAfterStep()
    {
        echo 'Is passing';
    }

    #[Given('passing step')]
    #[Given('passing step with failing hook')]
    public function passingStep()
    {
        echo 'Is passing';
    }

    #[Given('failing step')]
    public function failingStep()
    {
        throw new RuntimeException('Failing');
    }
}
