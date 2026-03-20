<?php

declare (strict_types=1);
namespace Behat\Config\Converter;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Class_Const_Fetch;
use Php_Parser\Node\Name;
use Php_Parser\Node\Name\Fully_Qualified;
use Php_Parser\Node_Visitor_Abstract;
class Used_Classes_Collector extends Node_Visitor_Abstract
{
    /**
     * @var array <string, class-string>
     */
    private array $used_classes = [];
    public function enter_node(Node $node): ?Node
    {
        if ($node instanceof Fully_Qualified) {
            return $this->import_class($node);
        }
        if ($node instanceof Class_Const_Fetch && $node->class instanceof Name) {
            // E.g. a `MyClass::class` argument
            return new Class_Const_Fetch($this->import_class($node->class), $node->name, $node->get_attributes());
        }
        return null;
    }
    private function import_class(Name $node): Name
    {
        $class_name = $node->to_string();
        $short_name = $node->get_last();
        $this->used_classes[$short_name] = $class_name;
        return new Name($short_name);
    }
    /**
     * @return array <string, class-string>
     */
    public function get_used_classes(): array
    {
        return $this->used_classes;
    }
}