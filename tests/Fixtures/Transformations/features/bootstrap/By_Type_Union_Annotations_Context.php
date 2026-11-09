<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class By_Type_Union_Annotations_Context implements Context
{
    /** @Transform */
    public function userFromName(string $name): User|int
    {
        return $name === 'nobody' ? 0 : new User($name);
    }

    /**
     * @Given I am :user
     * @Given she is :user
     */
    public function iAm(User $user): void
    {
    }
}
