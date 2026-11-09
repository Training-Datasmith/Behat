<?php

use Behat\Behat\Context\Translatable_Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;

class Arguments_Context_Attributes implements Translatable_Context
{
    private $index;

    #[Transform('/^(0|[1-9]\d*)(?:ую|ью|ю|ый|ой|ий|ый|й|ом|ем|м)?$/')]
    public function castToInt($number)
    {
        return intval($number) < PHP_INT_MAX ? intval($number) : $number;
    }

    #[Given('I pick the :index thing')]
    public function iPickThing($index)
    {
        $this->index = $index;
    }

    #[Then('/^the index should be "([^"]*)"$/')]
    public function theIndexShouldBe($value)
    {
        PHPUnit\Framework\Assert::assertSame($value, $this->index);
    }

    public static function getTranslationResources()
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff'];
    }
}
