<?php

use Behat\Step\Then;

class Parent_Context_Attributes
{
    #[Then(':token should be :value')]
    public function shouldBe($token, $value): void
    {
    }
}
