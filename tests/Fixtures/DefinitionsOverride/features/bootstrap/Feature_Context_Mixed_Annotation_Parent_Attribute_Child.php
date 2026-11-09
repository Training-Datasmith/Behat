<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class Feature_Context_Mixed_Annotation_Parent_Attribute_Child extends Parent_Context_Annotations implements Context
{
    #[Then(':token should be equal to :value')]
    public function shouldBe($token, $value): void
    {
    }
}
