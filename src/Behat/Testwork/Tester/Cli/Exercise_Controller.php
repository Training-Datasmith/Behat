<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Specification\Specification_Finder;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\Suite_Repository;
use Behat\Testwork\Tester\Exception\Wrong_Paths_Exception;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes exercise.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Exercise_Controller implements Controller
{
    /**
     * Initializes controller.
     *
     * @param bool             $skip
     */
    public function __construct(private readonly Suite_Repository $suite_repository, private readonly Specification_Finder $specification_finder, private readonly Exercise $exercise, private readonly Result_Interpreter $result_interpreter, private $skip = false)
    {
    }

    public function configure(Command $command): void
    {
        $locators_examples = implode(PHP_EOL, array_map(fn (string $locator): string => '- ' . $locator, $this->specification_finder->get_example_locators()));
        $command->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Optional path(s) to execute. Could be:' . PHP_EOL . $locators_examples)->addOption('--dry-run', null, InputOption::VALUE_NONE, 'Invokes formatters without executing the tests and hooks.')->addOption('--allow-no-tests', null, InputOption::VALUE_NONE, 'Will not fail if no specifications are found.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = $this->extract_unique_paths($input);
        $specs = $this->find_specifications($paths);
        $result = $this->test_specifications($input, $specs);
        if ($paths !== null && !$input->getOption('allow-no-tests') && Test_Results::NO_TESTS === $result->get_result_code()) {
            throw new Wrong_Paths_Exception(sprintf('No specifications found at path(s) `%s`. This might be because of incorrect paths configuration in your `suites`.', implode(', ', $paths)), $paths);
        }

        return $this->result_interpreter->interpret_result($result);
    }

    /**
     * Finds exercise specifications.
     *
     * @param list<string>|null $paths
     *
     * @return Specification_Iterator[]
     */
    private function find_specifications(?array $paths): array
    {
        $available_suites = $this->get_available_suites();
        if ($paths === null) {
            return $this->find_suites_specifications($available_suites, null);
        }
        $specifications = [];
        foreach ($paths as $path) {
            $specifications = array_merge($specifications, $this->find_suites_specifications($available_suites, $path));
        }

        return $specifications;
    }

    /**
     * Tests exercise specifications.
     *
     * @param Specification_Iterator[] $specifications
     */
    private function test_specifications(InputInterface $input, array $specifications): Test_Result
    {
        $skip = $input->getOption('dry-run') || $this->skip;
        $setup = $this->exercise->set_up($specifications, $skip);
        $skip = !$setup->is_successful() || $skip;
        $test_result = $this->exercise->test($specifications, $skip);
        $teardown = $this->exercise->tear_down($specifications, $skip, $test_result);
        $result = new Integer_Test_Result($test_result->get_result_code());

        return new Test_With_Setup_Result($setup, $result, $teardown);
    }

    /**
     * Returns all currently available suites.
     *
     * @return Suite[]
     */
    private function get_available_suites()
    {
        return $this->suite_repository->get_suites();
    }

    /**
     * Finds specification iterators for all provided suites using locator.
     *
     * @param Suite[]     $suites
     *
     * @return Specification_Iterator[]
     */
    private function find_suites_specifications(array $suites, ?string $locator): array
    {
        return $this->specification_finder->find_suites_specifications($suites, $locator);
    }

    /**
     * Extracts unique paths from input argument. Returns null if no paths were supplied.
     */
    private function extract_unique_paths(InputInterface $input): ?array
    {
        $paths = $input->getArgument('paths') ?: null;
        if ($paths === null) {
            return null;
        }

        return array_values(array_unique($paths));
    }
}
