<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class Unregistered_Step_Context implements Context
{
    #[Given('a step')]
    public function aStep(Service4 $s)
    {
    }
}
