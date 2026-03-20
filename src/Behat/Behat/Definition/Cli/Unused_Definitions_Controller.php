<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Cli;

use Behat\Behat\Definition\Call\Runtime_Definition;
use Behat\Behat\Definition\Definition_Repository;
use Behat\Behat\Definition\Printer\Unused_Definition_Printer;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\Suite_Tested;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Prints unused definitions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Unused_Definitions_Controller implements Controller
{
    /**
     * @var array <string, array{definition: RuntimeDefinition, used: bool}>
     */
    private array $definition_usage = [];
    public function __construct(private readonly Definition_Repository $definition_repository, private readonly Event_Dispatcher_Interface $event_dispatcher, private readonly Unused_Definition_Printer $printer, private bool $print_unused_definitions)
    {
    }
    public function configure(Command $command): void
    {
        $command->add_option('--print-unused-definitions', null, Input_Option::VALUE_NONE, 'Reports definitions that were never used.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        if ($input->get_option('print-unused-definitions')) {
            $this->print_unused_definitions = true;
        }
        if ($this->print_unused_definitions) {
            $this->event_dispatcher->add_listener(Suite_Tested::AFTER, $this->register_definition_usages(...), -999);
            $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->print_unused_definitions(...), -999);
        }
        return null;
    }
    public function register_definition_usages(After_Suite_Tested $event): void
    {
        $environment_definitions = $this->definition_repository->get_environment_definitions($event->get_environment());
        foreach ($environment_definitions as $definition) {
            if ($definition instanceof Runtime_Definition) {
                $path = $definition->get_path();
                if (!isset($this->definition_usage[$path])) {
                    $this->definition_usage[$path] = ['definition' => $definition, 'used' => $definition->has_been_used()];
                } elseif ($definition->has_been_used()) {
                    $this->definition_usage[$path]['used'] = true;
                }
            }
        }
    }
    public function print_unused_definitions(): void
    {
        $unused_definitions = array_filter($this->definition_usage, fn(array $definition): bool => $definition['used'] === false);
        $unused_definitions = array_column($unused_definitions, 'definition');
        $this->printer->print_unused_definitions($unused_definitions);
    }
}