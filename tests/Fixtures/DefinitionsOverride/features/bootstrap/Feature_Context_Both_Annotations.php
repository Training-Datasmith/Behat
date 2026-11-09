<?php

use Behat\Behat\Context\Context;

class Feature_Context_Both_Annotations extends Parent_Context_Annotations implements Context
{
    /**
     * @Then :token should be equal to :value
     */
    public function shouldBe($token, $value): void
    {
    }
}
