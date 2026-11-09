<?php

use Behat\Behat\Context\Context;

class Broken_Regex_Annotations implements Context
{
    /**
     * @Then /I am (foo/
     */
    public function invalidRegex(): void
    {
    }
}
