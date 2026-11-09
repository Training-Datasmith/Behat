<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;

final class Second_Context implements Context
{
    private Shared_Service $service;

    public function __construct(Shared_Service $service)
    {
        $this->service = $service;
    }

    #[Then('service should have a state of :number in second context')]
    public function checkState(string $number): void
    {
        PHPUnit\Framework\Assert::assertSame($number, $this->service->number);
    }
}
