<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use Behat\Testwork\Service_Container\Configuration\Configuration_Loader;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides debug information about the current environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Debug_Command extends BaseCommand
{
    /**
     * Initialises command.
     */
    public function __construct(private readonly Application $application, private readonly Configuration_Loader $configuration_loader, private readonly Extension_Manager $extension_manager)
    {
        parent::__construct('debug');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf('%s version %s', $this->application->getName(), $this->application->getVersion()));
        $output->writeln('');
        $debug = $this->configuration_loader->debug_information();
        $output->writeln('--- configuration');
        $output->writeln(sprintf('    environment variable (%s): %s', $debug['environment_variable_name'], $debug['environment_variable_content']));
        $output->writeln(sprintf('    configuration file: %s', $debug['configuration_file_path']));
        $output->writeln('');
        $debug = $this->extension_manager->debug_information();
        $output->writeln('--- extensions');
        $output->writeln(sprintf('    extensions loaded: %s', count($debug['extensions_list']) > 0 ? implode(', ', $debug['extensions_list']) : 'none'));
        $output->writeln('');

        return 0;
    }
}
