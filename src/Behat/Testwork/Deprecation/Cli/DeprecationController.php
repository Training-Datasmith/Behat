<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Deprecation\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Deprecation\Deprecation_Printer;
use Behat\Testwork\Deprecation\Result\Interpretation\Deprecation_Interpretation;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
/**
 * CLI controller that prints deprecations at the end of the test run.
 */
final class Deprecation_Controller implements Controller, Event_Subscriber_Interface
{
    private ?Output_Interface $output = null;
    public function __construct(private readonly Deprecation_Collector $collector, private readonly Deprecation_Printer $printer, private readonly Result_Interpreter $result_interpreter, private bool $print_deprecations, private bool $fail_on_deprecations = false)
    {
    }
    public static function get_subscribed_events(): array
    {
        return [Exercise_Completed::AFTER => ['onAfterExercise', -100]];
    }
    public function configure(Command $command): void
    {
        $command->add_option('--print-behat-deprecations', null, Input_Option::VALUE_NONE, 'Print Behat deprecation warnings at the end of the test run.');
        $command->add_option('--fail-on-behat-deprecations', null, Input_Option::VALUE_NONE, 'Exit with error code if Behat deprecations were triggered.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        $this->output = $output;
        if ($input->get_option('print-behat-deprecations')) {
            $this->print_deprecations = true;
        }
        if ($input->get_option('fail-on-behat-deprecations')) {
            $this->fail_on_deprecations = true;
            $this->print_deprecations = true;
        }
        if ($this->fail_on_deprecations) {
            $this->result_interpreter->register_result_interpretation(new Deprecation_Interpretation($this->collector));
        }
        return null;
    }
    public function on_after_exercise(): void
    {
        $this->collector->unregister();
        if ($this->print_deprecations && $this->output instanceof Output_Interface) {
            $this->printer->print_deprecations($this->output);
        }
    }
}