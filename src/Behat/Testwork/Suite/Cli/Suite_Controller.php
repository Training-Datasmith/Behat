<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\Exception\Suite_Not_Found_Exception;
use Behat\Testwork\Suite\Suite_Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets up registered test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Suite_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private readonly Suite_Registry $registry, private array $suite_configurations)
    {
    }

    public function configure(Command $command): void
    {
        $command->addOption('--suite', '-s', InputOption::VALUE_REQUIRED, 'Only execute a specific suite.');
    }

    public function execute(InputInterface $input, OutputInterface $output): null
    {
        $exercise_suite_name = $input->getOption('suite');
        if (null !== $exercise_suite_name && !isset($this->suite_configurations[$exercise_suite_name])) {
            throw new Suite_Not_Found_Exception(sprintf('`%s` suite is not found or has not been properly registered.', $exercise_suite_name), $exercise_suite_name);
        }
        foreach ($this->suite_configurations as $name => $config) {
            if (null !== $exercise_suite_name && $exercise_suite_name !== $name) {
                continue;
            }
            $this->registry->register_suite_configuration($name, $config['type'], $config['settings']);
        }

        return null;
    }
}
