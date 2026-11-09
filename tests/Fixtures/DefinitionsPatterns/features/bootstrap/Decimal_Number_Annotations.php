<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class Decimal_Number_Annotations implements Context
{
    /**
     * @Given I have a package v:version
     */
    public function multipleWrongNamedParameters($version): void
    {
        Assert::assertEquals('2.5', $version);
    }
}
