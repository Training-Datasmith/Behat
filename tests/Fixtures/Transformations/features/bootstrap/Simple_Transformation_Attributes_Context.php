<?php

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class Simple_Transformation_Attributes_Context implements Context
{
    #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
    public function createUserFromUsername(string $username, int $age = 20): User
    {
        return new User($username, $age);
    }

    #[Transform('/^(yes|no)$/')]
    public function castYesOrNoToBoolean($expected): bool
    {
        return 'yes' === $expected;
    }
}
