<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class First_Context_Typehinted implements Context
{
    public function __construct(Parent_Class $parent, Child_Class $child)
    {
    }

    #[Given('foo')]
    public function foo(): void
    {
    }
}
