<?php

use Behat\Behat\Context\Translatable_Context;
use Behat\Step\Given;

class Translated_Definitions_Context implements Translatable_Context
{
    #[Given('/^I have entered (\d+) into calculator$/')]
    public function iHaveEnteredIntoCalculator($number)
    {
    }

    #[Given('/^I have clicked "+"$/')]
    public function iHaveClickedPlus()
    {
    }

    public static function getTranslationResources()
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff'];
    }
}
