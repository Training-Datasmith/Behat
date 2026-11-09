<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class Multiple_Transformations_In_One_Function_Attributes_Context implements Context
{
    #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
    #[Transform(':user')]
    public function createUserFromUsername(string $username, int $age = 20): User
    {
        return new User($username, $age);
    }
}
