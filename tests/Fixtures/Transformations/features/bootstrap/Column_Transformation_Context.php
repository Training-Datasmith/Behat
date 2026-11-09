<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class Column_Transformation_Context implements Context
{
    #[Transform('column:user,other user')]
    public function convertUsernameToUserInColumn(string $name): User
    {
        return new User($name);
    }

    #[Transform('column:логин')]
    public function convertUsernameInRussianToUserInColumn(string $name): User
    {
        return new User($name);
    }

    #[Transform('column:username')]
    public function convertUsernameToUserWithUsernameHeading(string $name): User
    {
        throw new Exception('This should not be called');
    }

    #[Transform('column:hex age')]
    public function convertHexAgeToAge(string $hexAge): int
    {
        return hexdec($hexAge);
    }
}
