<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class First_Context_Mixed_Args implements Context
{
    public function __construct(stdClass $service, string $bar)
    {
    }

    #[Given('foo')]
    public function foo(): void
    {
    }
}
