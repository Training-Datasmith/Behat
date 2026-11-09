<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Translatable_Context;
use Behat\Behat\Exception\Pending_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Translatable_Definitions_Context implements Context, Translatable_Context
{
    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new Pending_Exception();
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new Pending_Exception();
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new Pending_Exception();
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new Pending_Exception();
    }

    public static function getTranslationResources(): array
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff'];
    }
}
