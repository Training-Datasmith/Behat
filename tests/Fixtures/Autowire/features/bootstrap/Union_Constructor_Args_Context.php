<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class Union_Constructor_Args_Context implements Context
{
    public function __construct(Service1|Service2 $s)
    {
    }

    #[Given('a step')]
    public function aStep()
    {
    }
}
