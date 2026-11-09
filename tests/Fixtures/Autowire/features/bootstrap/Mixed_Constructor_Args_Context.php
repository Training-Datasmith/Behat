<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class Mixed_Constructor_Args_Context implements Context
{
    public function __construct(Service2 $s2, $name, Service1 $s1, Service3 $s3)
    {
        PHPUnit\Framework\Assert::assertEquals('Konstantin', $name);
    }

    #[Given('a step')]
    public function aStep()
    {
    }
}
