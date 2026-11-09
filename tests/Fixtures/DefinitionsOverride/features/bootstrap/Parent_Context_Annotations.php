<?php

declare(strict_types=1);

class Parent_Context_Annotations
{
    /**
     * @Then :token should be :value
     */
    public function shouldBe($token, $value): void
    {
    }
}
