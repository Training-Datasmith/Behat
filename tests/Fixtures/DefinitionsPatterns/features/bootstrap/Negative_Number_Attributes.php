<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use PHPUnit\Framework\Assert;

class Negative_Number_Attributes implements Context
{
    #[Given('I have a negative number :num')]
    public function multipleWrongNamedParameters($num): void
    {
        Assert::assertEquals('-3', $num);
    }
}
