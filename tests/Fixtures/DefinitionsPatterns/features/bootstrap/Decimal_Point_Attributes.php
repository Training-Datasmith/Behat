<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class Decimal_Point_Attributes implements Context
{
    #[Then(':token should have value of £:value')]
    public function shouldHaveValueOf($token, $value): void
    {
        echo $value;
    }
}
