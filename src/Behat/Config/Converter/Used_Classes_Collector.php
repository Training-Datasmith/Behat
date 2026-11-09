<?php

declare(strict_types=1);

namespace Behat\Config\Converter;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

class Used_Classes_Collector extends NodeVisitorAbstract
{
    /**
     * @var array <string, class-string>
     */
    private array $used_classes = [];

    public function enter_node(Node $node): ?Node
    {
        if ($node instanceof FullyQualified) {
            return $this->import_class($node);
        }
        if ($node instanceof ClassConstFetch && $node->class instanceof Name) {
            // E.g. a `MyClass::class` argument
            return new ClassConstFetch($this->import_class($node->class), $node->name, $node->get_attributes());
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
