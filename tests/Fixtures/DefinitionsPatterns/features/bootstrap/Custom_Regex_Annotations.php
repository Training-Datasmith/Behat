<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class Custom_Regex_Annotations implements Context
{
    /**
     * @Given /^I have entered (\d+)$/
     */
    public function iHaveEntered($number): void
    {
    }
}
