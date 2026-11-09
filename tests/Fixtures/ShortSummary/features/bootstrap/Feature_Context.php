<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Hook\After_Suite;
use Behat\Step\Given;

class Feature_Context implements Context
{
    #[Given('I have a passing step')]
    public function iHaveAPassingStep()
    {
    }

    #[Given('I have a pending step')]
    public function iHaveAPendingStep()
    {
        throw new Pending_Exception();
    }

    #[Given('I have a failing step')]
    public function iHaveAFailingStep()
    {
        throw new Exception('This scenario has a failed step');
    }

    #[After_Suite]
    public static function tearDown()
    {
        throw new Exception('This suite has a failed teardown');
    }
}
