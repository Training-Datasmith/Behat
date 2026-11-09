<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

use Behat\Config\Config_Converter_Interface;
use Behat\Config\Converter\Config_Converter_Tools;
use PhpParser\Node\Expr;

class Filter implements Filter_Interface, Config_Converter_Interface
{
    public function __construct(private readonly string $name, private readonly string $value)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        $filter_object = Config_Converter_Tools::create_object(static::class);
        Config_Converter_Tools::add_arguments_to_constructor([$this->value], $filter_object);

        return $filter_object;
    }
}
