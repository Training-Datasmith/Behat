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

use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Service_Container\Configuration\Configuration_Loader;
use Behat\Testwork\Service_Container\Container_Loader;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use Behat\Testwork\Service_Container\Extension_Manager;
use Composer\XdebugHandler\XdebugHandler;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Extends Symfony console application with testwork functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Application extends BaseApplication
{
    /**
     * Initializes application.
     */
    public function __construct(string $name, string $version, private readonly Configuration_Loader $configuration_loader, private readonly Extension_Manager $extension_manager)
    {
        putenv('COLUMNS=9999');
        parent::__construct($name, $version);
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    public function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.'), new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'), new InputOption('--verbose', '-v', InputOption::VALUE_OPTIONAL, 'Increase verbosity of exceptions.' . PHP_EOL . 'Use -vv or --verbose=2 to display backtraces in addition to exceptions.'), new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'), new InputOption('--convert-config', null, InputOption::VALUE_NONE, 'Convert the configuration to the PHP format.'), new InputOption('--config-reference', null, InputOption::VALUE_NONE, 'Display the configuration reference.'), new InputOption('--debug', null, InputOption::VALUE_NONE, 'Provide debugging information about current environment.'), new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display version.'), new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question.'), new InputOption('--colors', null, InputOption::VALUE_NONE, 'Force ANSI color in the output. By default color support is' . PHP_EOL . 'guessed based on your platform and the output if not specified.'), new InputOption('--no-colors', null, InputOption::VALUE_NONE, 'Force no ANSI color in the output.'), new InputOption('--xdebug', null, InputOption::VALUE_NONE, 'Allow Xdebug to run.')]);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // Register deprecation collector as early as possible
        Deprecation_Collector::get_instance()->register();
        $is_xdebug_allowed = $input->hasParameterOption('--xdebug') || extension_loaded('xdebug') && xdebug_is_debugger_active();
        if (!$is_xdebug_allowed) {
            $xdebug_handler = new XdebugHandler('behat');
            $xdebug_handler->setPersistent();
            $xdebug_handler->check();
            unset($xdebug_handler);
        }
        // xdebug's default nesting level of 100 is not enough
        if (extension_loaded('xdebug') && !str_contains(ini_get('disable_functions'), 'ini_set')) {
            $old_value = ini_get('xdebug.max_nesting_level');
            if ($old_value === false || $old_value < 256) {
                ini_set('xdebug.max_nesting_level', 256);
            }
        }
        if ($input->hasParameterOption(['--config-reference'])) {
            $input = new ArrayInput(['--config-reference' => true]);
        }
        if ($path = $input->getParameterOption(['--config', '-c'])) {
            if (!is_file($path)) {
                throw new Configuration_Loading_Exception('The requested config file does not exist');
            }
            $this->configuration_loader->set_configuration_file_path($path);
        }
        $command = $this->create_command($input, $output);
        $this->addCommands([$command]);

        return parent::doRun($input, $output);
    }

    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Dump_Reference_Command($this->extension_manager);
        $commands[] = new Debug_Command($this, $this->configuration_loader, $this->extension_manager);
        $commands[] = new Convert_Config_Command($this->configuration_loader);

        return $commands;
    }

    /**
     * Configures container based on provided config file and profile.
     */
    private function load_configuration(InputInterface $input): array
    {
        $profile = $input->getParameterOption(['--profile', '-p']) ?: 'default';

        return $this->configuration_loader->load_configuration($profile);
    }

    /**
     * Creates main command for application.
     */
    private function create_command(InputInterface $input, OutputInterface $output): SymfonyCommand
    {
        return $this->create_container($input, $output)->get('cli.command');
    }

    /**
     * Creates container instance, loads extensions and freezes it.
     */
    private function create_container(InputInterface $input, OutputInterface $output): ContainerBuilder
    {
        $base_path = rtrim($this->get_base_path(), DIRECTORY_SEPARATOR);
        $container = new ContainerBuilder();
        $container->setParameter('cli.command.name', $this->getName());
        $container->setParameter('paths.base', $base_path);
        $container->set('cli.input', $input);
        $container->set('cli.output', $output);
        $extension = new Container_Loader($this->extension_manager);
        $extension->load($container, $this->load_configuration($input));
        $container->addObjectResource($extension);
        $container->compile(true);

        return $container;
    }

    /**
     * Returns base path.
     *
     * @return string
     */
    private function get_base_path()
    {
        if ($config_path = $this->configuration_loader->get_configuration_file_path()) {
            return realpath(dirname($config_path));
        }

        return realpath(getcwd());
    }

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input): string
    {
        if ($input->hasParameterOption(['--config-reference'])) {
            return 'dump-reference';
        }
        if ($input->hasParameterOption(['--debug'])) {
            return 'debug';
        }
        if ($input->hasParameterOption(['--convert-config'])) {
            return 'convert-config';
        }

        return $this->getName();
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        if ($input->hasParameterOption(['--colors'])) {
            $output->setDecorated(true);
        } elseif ($input->hasParameterOption(['--no-colors'])) {
            $output->setDecorated(false);
        }
        parent::configureIO($input, $output);
    }
}
