<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class Token_Start_Annotations implements Context
{
    /**
     * @Then :token should be :value
     */
    public function shouldBe($token, $value): void
    {
    }
}
