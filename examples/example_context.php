<?php

declare(strict_types=1);

/**
 * Example: A simple Behat step-definition context using PHP 8 attributes.
 *
 * Feature file (features/greeting.feature):
 *
 *   Feature: Greeting
 *     Scenario: Greeting a user
 *       Given there is a user "Alice"
 *       When I greet the user
 *       Then I should see "Hello, Alice!"
 *
 * Run with: vendor/bin/behat
 */

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

final class GreetingContext implements Context
{
    private string $userName = '';
    private string $output = '';

    /**
     * Sets up a user by name for subsequent steps.
     *
     * @param string $name the user's display name
     */
    #[Given('there is a user :name')]
    public function thereIsAUser(string $name): void
    {
        $this->userName = $name;
    }

    /**
     * Performs the greeting action.
     */
    #[When('I greet the user')]
    public function iGreetTheUser(): void
    {
        $this->output = sprintf('Hello, %s!', $this->userName);
    }

    /**
     * Asserts that the output matches the expected string.
     *
     * @param string $expected the expected greeting string
     *
     * @throws RuntimeException if the output does not match
     */
    #[Then('I should see :expected')]
    public function iShouldSee(string $expected): void
    {
        if ($this->output !== $expected) {
            throw new RuntimeException(
                sprintf('Expected "%s" but got "%s".', $expected, $this->output)
            );
        }
    }
}
