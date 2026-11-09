<?php

declare(strict_types=1);
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * CLI controller that prints deprecations at the end of the test run.
 */
final class Deprecation_Controller implements Controller, EventSubscriberInterface
{
    private ?OutputInterface $output = null;

    public function __construct(private readonly Deprecation_Collector $collector, private readonly Deprecation_Printer $printer, private readonly Result_Interpreter $result_interpreter, private bool $print_deprecations, private bool $fail_on_deprecations = false)
    {
    }

    public static function get_subscribed_events(): array
    {
        return [Exercise_Completed::AFTER => ['on_after_exercise', -100]];
    }

    public static function getSubscribedEvents(): array
    {
        return self::get_subscribed_events();
    }

    public function configure(Command $command): void
    {
        $command->addOption('--print-behat-deprecations', null, InputOption::VALUE_NONE, 'Print Behat deprecation warnings at the end of the test run.');
        $command->addOption('--fail-on-behat-deprecations', null, InputOption::VALUE_NONE, 'Exit with error code if Behat deprecations were triggered.');
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->output = $output;
        if ($input->getOption('print-behat-deprecations')) {
            $this->print_deprecations = true;
        }
        if ($input->getOption('fail-on-behat-deprecations')) {
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
        if ($this->print_deprecations && $this->output instanceof OutputInterface) {
            $this->printer->print_deprecations($this->output);
        }
    }
}
