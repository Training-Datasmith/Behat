<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class Unregistered_Constructor_Context implements Context
{
    public function __construct(Service4 $s)
    {
    }

    #[Given('a step')]
    public function aStep()
    {
    }
}
