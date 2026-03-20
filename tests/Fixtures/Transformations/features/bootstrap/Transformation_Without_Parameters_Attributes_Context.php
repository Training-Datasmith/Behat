<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class TransformationWithoutParametersAttributesContext implements Context
{
    #[Transform]
    public function userFromName(string $username): User
    {
        return new User($username);
    }
}
