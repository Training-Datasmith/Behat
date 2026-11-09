<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class Negative_Number_Annotations implements Context
{
    /**
     * @Given I have a negative number :num
     */
    public function multipleWrongNamedParameters($num): void
    {
        Assert::assertEquals('-3', $num);
    }
}
