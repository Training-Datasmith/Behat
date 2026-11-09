<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class Feature_Context_Both_Attributes extends Parent_Context_Attributes implements Context
{
    #[Then(':token should be equal to :value')]
    public function shouldBe($token, $value): void
    {
    }
}
