<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Feature_Context_Attributes implements Context
{
    private int $value = 0;

    #[Given('/^Я ввел (\d+)$/')]
    public function iHaveEntered(int $number): void
    {
        $this->value = $number;
    }

    #[When('/^Я добавлю (\d+)$/')]
    public function iAdd(int $number): void
    {
        $this->value += $number;
    }

    #[Then('/^Я должен иметь (\d+)$/')]
    public function iShouldHave(int $number): void
    {
        if ($this->value !== $number) {
            throw new Exception(sprintf('Failed asserting that %d matches expected %d.', $this->value, $number));
        }
    }

    #[Given('/^Что-то еще не сделано$/')]
    public function somethingNotDone(): void
    {
        throw new Pending_Exception();
    }
}
