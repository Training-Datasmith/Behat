<?php

use Behat\Behat\Context\Context;

class Feature_Context_Mixed_Attribute_Parent_Annotation_Child extends Parent_Context_Attributes implements Context
{
    /**
     * @Then :token should be equal to :value
     */
    public function shouldBe($token, $value): void
    {
    }
}
