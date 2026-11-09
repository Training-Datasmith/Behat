<?php

use Behat\Behat\Context\Context;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class Empty_Parameter_Attributes implements Context
{
    #[When('I enter the string :input')]
    public function multipleWrongNamedParameters($input): void
    {
        Assert::assertEquals('', $input);
    }
}
