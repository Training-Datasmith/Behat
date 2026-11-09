<?php

declare(strict_types=1);

namespace Behat\Config;

interface Config_Interface
{
    public function to_array(): array;
}

class_alias(Config_Interface::class, __NAMESPACE__ . '\\ConfigInterface');
