<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet\Cli;

use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Step_Tested;
use Behat\Behat\Snippet\Printer\Console_Snippet_Printer;
use Behat\Behat\Snippet\Snippet_Registry;
use Behat\Behat\Snippet\Snippet_Writer;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Tester\Result\Test_Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Appends and prints snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Snippets_Controller implements Controller
{
    private ?\Symfony\Component\Console\Output\Output_Interface $output = null;
    /**
     * Initializes controller.
     */
    public function __construct(private readonly Snippet_Registry $registry, private readonly Snippet_Writer $writer, private readonly Console_Snippet_Printer $printer, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->add_option('--append-snippets', null, Input_Option::VALUE_NONE, 'Appends snippets for undefined steps into main context.')->add_option('--no-snippets', null, Input_Option::VALUE_NONE, 'Do not print snippets for undefined steps after stats.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        $this->event_dispatcher->add_listener(Step_Tested::AFTER, $this->register_undefined_step(...), -999);
        $this->output = $output;
        if ($input->get_option('append-snippets')) {
            $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->append_all_snippets(...), -999);
        }
        if (!$input->get_option('no-snippets') && !$input->get_option('append-snippets')) {
            $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->print_all_snippets(...), -999);
        }
        if (!$input->get_option('no-snippets')) {
            $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->print_snippet_generation_failures(...), -999);
        }
        if (!$input->get_option('no-snippets')) {
            $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->print_undefined_steps(...), -995);
        }
        return null;
    }
    /**
     * Registers undefined step.
     */
    public function register_undefined_step(After_Step_Tested $event): void
    {
        if (Test_Result::UNDEFINED === $event->get_test_result()->get_result_code()) {
            $this->registry->register_undefined_step($event->get_environment(), $event->get_step());
        }
    }
    /**
     * Appends all snippets to corresponding targets.
     */
    public function append_all_snippets(): void
    {
        $snippets = $this->registry->get_snippets();
        if ($snippets) {
            $this->output->writeln('');
        }
        $this->writer->append_snippets($snippets);
    }
    /**
     * Prints all snippets.
     */
    public function print_all_snippets(): void
    {
        $snippets = $this->registry->get_snippets();
        if ($snippets) {
            $this->output->writeln('');
        }
        $this->writer->print_snippets($this->printer, $snippets);
    }
    private function print_snippet_generation_failures(): void
    {
        $failures = $this->registry->get_generation_failures();
        if ($failures !== []) {
            $this->output->writeln('');
        }
        $this->printer->print_snippet_generation_failures($failures);
    }
    /**
     * Prints all undefined steps.
     */
    public function print_undefined_steps(): void
    {
        $undefined = $this->registry->get_undefined_steps();
        if ($undefined) {
            $this->output->writeln('');
        }
        $this->writer->print_undefined_steps($this->printer, $undefined);
    }
}