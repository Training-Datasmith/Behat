<?php

use Behat\Hook\After_Feature;
use Behat\Hook\Before_Feature;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class Feature_Hook_Attributes_Context implements Behat\Behat\Context\Context
{
    #[Before_Feature]
    public static function beforeFeature()
    {
        echo '= BEFORE FEATURE =';
    }

    #[After_Feature]
    public static function afterFeature()
    {
        echo '= AFTER FEATURE =';
    }

    #[Before_Feature('Fruit story')]
    public static function beforeFruitStory()
    {
        echo '= BEFORE FRUIT STORY =';
    }

    #[After_Feature('Fruit story')]
    public static function afterFruitStory()
    {
        echo '= AFTER FRUIT STORY =';
    }

    #[Given('I have :count apple(s)')]
    #[Given('I have :count banana(s)')]
    public function iHaveFruit($count)
    {
    }

    #[When('I eat :count apple(s)')]
    #[When('I eat :count banana(s)')]
    public function iEatFruit($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    #[Then('I should have :count banana(s)')]
    public function iShouldHaveFruit($count)
    {
    }
}
