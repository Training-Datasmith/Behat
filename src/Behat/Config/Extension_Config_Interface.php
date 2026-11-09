<?php

declare(strict_types=1);

namespace Behat\Config;

interface Extension_Config_Interface extends Config_Interface
{
    public function name(): string;
}
