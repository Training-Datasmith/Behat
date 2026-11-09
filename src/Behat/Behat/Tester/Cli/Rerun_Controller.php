<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Cli;

use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Caches failed scenarios and reruns only them if `--rerun` option provided.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Rerun_Controller implements Controller
{
    private readonly ?string $cache_path;
    private ?string $key = null;
    /**
     * @var array<string, string[]>
     */
    private array $lines = [];

    /**
     * Initializes controller.
     */
    public function __construct(private readonly EventDispatcherInterface $event_dispatcher, private readonly Result_Interpreter $result_interpreter, private readonly string $basepath, $cache_path)
    {
        $this->cache_path = null !== $cache_path ? rtrim($cache_path, DIRECTORY_SEPARATOR) : null;
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->addOption('--rerun', null, InputOption::VALUE_NONE, 'Re-run scenarios that failed during last execution, or run everything if there were no failures.');
        $command->addOption('--rerun-only', null, InputOption::VALUE_NONE, 'Re-run scenarios that failed during last execution, or exit if there were no failures.');
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->event_dispatcher->addListener(Scenario_Tested::AFTER, $this->collect_failed_scenario(...), -50);
        $this->event_dispatcher->addListener(Example_Tested::AFTER, $this->collect_failed_scenario(...), -50);
        $this->event_dispatcher->addListener(Exercise_Completed::AFTER, $this->write_cache(...), -50);
        $this->key = $this->generate_key($input);
        if (!$input->getOption('rerun') && !$input->getOption('rerun-only')) {
            return null;
        }
        if (!$this->get_file_name() || !file_exists($this->get_file_name())) {
            if ($input->getOption('rerun-only')) {
                $output->writeln('No failure found, exiting.');

                return 0;
            }

            return null;
        }
        $input->setArgument('paths', [$this->get_file_name()]);

        return null;
    }

    /**
     * Records scenario if it is failed.
     */
    public function collect_failed_scenario(After_Scenario_Tested $event): void
    {
        if (!$this->get_file_name()) {
            return;
        }
        if ($this->result_interpreter->interpret_result($event->get_test_result()) === Result_Interpreter::PASS) {
            return;
        }
        $feature = $event->get_feature();
        $scenario = $event->get_scenario();
        $suitename = $event->get_suite()->get_name();
        $this->lines[$suitename][] = $feature->getFile() . ':' . $scenario->getLine();
    }

    /**
     * Writes failed scenarios cache.
     */
    public function write_cache(): void
    {
        if (!$this->get_file_name()) {
            return;
        }
        if (file_exists($this->get_file_name())) {
            unlink($this->get_file_name());
        }
        if (0 === count($this->lines)) {
            return;
        }
        file_put_contents($this->get_file_name(), json_encode($this->lines));
    }

    /**
     * Generates cache key.
     */
    private function generate_key(InputInterface $input): string
    {
        return md5($input->getParameterOption(['--profile', '-p']) . $input->getOption('suite') . implode(' ', $input->getOption('name')) . implode(' ', $input->getOption('tags')) . $input->getOption('role') . \implode('', $input->getArgument('paths')) . $this->basepath);
    }

    /**
     * Returns cache filename (if exists).
     */
    private function get_file_name(): ?string
    {
        if (null === $this->cache_path || null === $this->key) {
            return null;
        }
        if (!is_dir($this->cache_path)) {
            mkdir($this->cache_path, 0777);
        }

        return $this->cache_path . DIRECTORY_SEPARATOR . $this->key . '.rerun';
    }
}
