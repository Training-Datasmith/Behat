<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class Feature_Context implements Context
{
    #[Given('I have :num orange(s)')]
    public function iHaveOranges(int $num): void
    {
    }
}
