<?php

use Behat\Behat\Context\Context;

class String_With_Point_Annotations implements Context
{
    /**
     * @Then :token should have value of :first.:second
     */
    public function shouldHaveValueOf($token, $first, $second): void
    {
        echo $first . ' + ' . $second;
    }
}
