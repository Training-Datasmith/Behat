<?php

declare (strict_types=1);
namespace Behat\Config\Formatter;

use Behat\Config\Config_Interface;
interface Formatter_Config_Interface extends Config_Interface
{
    public function name(): string;
    public static function defaults(): array;
}