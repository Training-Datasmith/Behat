<?php

declare(strict_types=1);

namespace Behat\Config;

use PhpParser\Node\Expr;

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
