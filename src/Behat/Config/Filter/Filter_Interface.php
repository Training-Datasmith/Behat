<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

interface Filter_Interface
{
    public function name(): string;

    public function value(): string;
}
