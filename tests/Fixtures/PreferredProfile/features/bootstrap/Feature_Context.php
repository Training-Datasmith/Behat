<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class Feature_Context implements Context
{
    private int $result;
    private array $numbers;

    #[Given('/I have basic calculator/')]
    public function iHaveBasicCalculator(): void
    {
        $this->result = 0;
        $this->numbers = [];
    }

    #[Given('/I have entered (\\d+)/')]
    public function iHaveEntered(string $number): void
    {
        $this->numbers[] = (int) $number;
    }

    #[When('/I add/')]
    public function iAdd(): void
    {
        $this->result = array_sum($this->numbers);
        $this->numbers = [];
    }

    #[When('/I sub/')]
    public function iSub(): void
    {
        $this->result = array_shift($this->numbers);
        $this->result -= array_sum($this->numbers);
        $this->numbers = [];
    }

    #[Then('/The result should be (\\d+)/')]
    public function theResultShouldBe(string $result): void
    {
        Assert::assertEquals($result, $this->result);
    }
}
