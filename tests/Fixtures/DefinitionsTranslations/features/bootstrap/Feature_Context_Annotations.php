<?php

use Behat\Behat\Context\Translatable_Context;

class Feature_Context_Annotations implements Translatable_Context
{
    private $numbers = [];
    private $result = 0;

    /**
     * @Given /^I have entered (\d+) into calculator$/
     */
    public function iHaveEnteredIntoCalculator($number)
    {
        $this->numbers[] = intval($number);
    }

    /**
     * @Given /^I have clicked "+"$/
     */
    public function iHaveClickedPlus()
    {
        $this->result = array_sum($this->numbers);
    }

    /**
     * @Then /^I should see (\d+) on the screen$/
     */
    public function iShouldSeeOnTheScreen($result)
    {
        PHPUnit\Framework\Assert::assertEquals(intval($result), $this->result);
    }

    /** @Transform /"([^"]+)" user/ */
    public static function createUserFromUsername($username)
    {
        return (object) ['name' => $username];
    }

    /**
     * @Then /^the ("[^"]+" user) name should be "([^"]*)"$/
     */
    public function theUserUsername($user, $username)
    {
        PHPUnit\Framework\Assert::assertEquals($username, $user->name);
    }

    public static function get_translation_resources()
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff'];
    }
}
