<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Cli;

use Behat\Testwork\Service_Container\Configuration\Configuration_Tree;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Dumper\Yaml_Reference_Dumper;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Extends Symfony console application with testwork functionality.
 *
 * @author Christophe Coevoet <stof>
 */
final class Dump_Reference_Command extends Base_Command
{
    /**
     * Initializes dumper.
     */
    public function __construct(private readonly Extension_Manager $extension_manager)
    {
        parent::__construct('dump-reference');
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $dumper = new Yaml_Reference_Dumper();
        $config_tree = new Configuration_Tree();
        $output->writeln($dumper->dump_node($config_tree->get_config_tree($this->extension_manager->get_extensions())));
        return 0;
    }
}