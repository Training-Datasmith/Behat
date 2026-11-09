<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class Empty_Parameter_Annotations implements Context
{
    /**
     * @When I enter the string :input
     */
    public function multipleWrongNamedParameters($input): void
    {
        Assert::assertEquals('', $input);
    }
}
