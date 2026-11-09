<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class Feature_Context_Inherit_Annotations extends Parent_Context_Annotations implements Context
{
    public function shouldBe($token, $value): void
    {
    }
}
