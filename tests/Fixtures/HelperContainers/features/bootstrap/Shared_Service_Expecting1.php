<?php

declare(strict_types=1);

final class Shared_Service_Expecting1 extends Shared_Service
{
    public function __construct(int $arg)
    {
        if (1 !== $arg) {
            throw new InvalidArgumentException();
        }
    }
}
