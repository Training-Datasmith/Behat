<?php

declare (strict_types=1);
namespace Behat\Config;

use Php_Parser\Node\Expr;
/**
 * @internal
 */
interface Config_Converter_Interface
{
    /**
     * @internal
     */
    public function to_php_expr(): Expr;
}