<?php

class Shared_Service_With_Factory extends Shared_Service
{
    private function __construct()
    {
    }

    public static function factoryMethod(): self
    {
        return new self();
    }
}
