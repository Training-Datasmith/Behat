<?php

declare (strict_types=1);
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
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
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
     *
     * @param string|null $cachePath
     */
    public function __construct(private readonly Event_Dispatcher_Interface $event_dispatcher, private readonly Result_Interpreter $result_interpreter, private readonly string $basepath, $cache_path)
    {
        $this->cache_path = null !== $cache_path ? rtrim($cache_path, DIRECTORY_SEPARATOR) : null;
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->add_option('--rerun', null, Input_Option::VALUE_NONE, 'Re-run scenarios that failed during last execution, or run everything if there were no failures.');
        $command->add_option('--rerun-only', null, Input_Option::VALUE_NONE, 'Re-run scenarios that failed during last execution, or exit if there were no failures.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        $this->event_dispatcher->add_listener(Scenario_Tested::AFTER, $this->collect_failed_scenario(...), -50);
        $this->event_dispatcher->add_listener(Example_Tested::AFTER, $this->collect_failed_scenario(...), -50);
        $this->event_dispatcher->add_listener(Exercise_Completed::AFTER, $this->write_cache(...), -50);
        $this->key = $this->generate_key($input);
        if (!$input->get_option('rerun') && !$input->get_option('rerun-only')) {
            return null;
        }
        if (!$this->get_file_name() || !file_exists($this->get_file_name())) {
            if ($input->get_option('rerun-only')) {
                $output->writeln('No failure found, exiting.');
                return 0;
            }
            return null;
        }
        $input->set_argument('paths', [$this->get_file_name()]);
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
        $this->lines[$suitename][] = $feature->get_file() . ':' . $scenario->get_line();
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
    private function generate_key(Input_Interface $input): string
    {
        return md5($input->get_parameter_option(['--profile', '-p']) . $input->get_option('suite') . implode(' ', $input->get_option('name')) . implode(' ', $input->get_option('tags')) . $input->get_option('role') . \implode('', $input->get_argument('paths')) . $this->basepath);
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