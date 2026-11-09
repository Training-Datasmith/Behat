<?php

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class Transformation_Without_Parameters_Attributes_Context implements Context
{
    #[Transform]
    public function userFromName(string $username): User
    {
        return new User($username);
    }
}
