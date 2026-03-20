<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Service_Container\Configuration;

use Behat\Testwork\Service_Container\Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Node_Interface;
/**
 * Builds configuration tree using provided lists of core and custom extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Configuration_Tree
{
    /**
     * Generates the configuration tree.
     *
     * @param Extension[] $extensions
     */
    public function get_config_tree(array $extensions): Node_Interface
    {
        $tree_builder = new Tree_Builder('testwork');
        /** @var ArrayNodeDefinition $rootNode */
        $root_node = $tree_builder->get_root_node();
        foreach ($extensions as $extension) {
            $extension->configure($root_node->children()->array_node($extension->get_config_key()));
        }
        return $tree_builder->build_tree();
    }
}