<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;

class Feature_Context_Background_Failing implements Context
{
    #[Given('/^.*$/')]
    public function anything(?TableNode $table = null): void
    {
        throw new Pending_Exception();
    }
}
