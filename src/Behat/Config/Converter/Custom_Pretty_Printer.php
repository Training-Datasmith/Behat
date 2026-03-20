<?php

declare (strict_types=1);
namespace Behat\Config\Converter;

use Php_Parser\Node\Expr\Array_;
use Php_Parser\Node\Expr\Method_Call;
use Php_Parser\Pretty_Printer\Standard;
class Custom_Pretty_Printer extends Standard
{
    protected function p_expr_array(Array_ $node): string
    {
        $printed_array = '[';
        $printed_array .= $this->p_comma_separated_multiline($node->items, true);
        return $printed_array . ($this->nl . ']');
    }
    protected function p_expr_method_call(Method_Call $node): string
    {
        $result = $this->p_dereference_lhs($node->var);
        $this->indent();
        $result .= $this->nl . '->' . $this->p_object_property($node->name) . '(';
        if (count($node->args) > 1) {
            $result .= $this->p_comma_separated_multiline($node->args, false) . $this->nl;
        } else {
            $result .= $this->p_comma_separated($node->args);
        }
        $result .= ')';
        $this->outdent();
        return $result;
    }
}