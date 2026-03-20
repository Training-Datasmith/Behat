<?php

declare (strict_types=1);
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
use Composer\Xdebug_Handler\Xdebug_Handler;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\Array_Input;
use Symfony\Component\Console\Input\Input_Definition;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
/**
 * Extends Symfony console application with testwork functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Application extends Base_Application
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
    public function get_default_input_definition(): Input_Definition
    {
        return new Input_Definition([new Input_Option('--profile', '-p', Input_Option::VALUE_REQUIRED, 'Specify config profile to use.'), new Input_Option('--config', '-c', Input_Option::VALUE_REQUIRED, 'Specify config file to use.'), new Input_Option('--verbose', '-v', Input_Option::VALUE_OPTIONAL, 'Increase verbosity of exceptions.' . PHP_EOL . 'Use -vv or --verbose=2 to display backtraces in addition to exceptions.'), new Input_Option('--help', '-h', Input_Option::VALUE_NONE, 'Display this help message.'), new Input_Option('--convert-config', null, Input_Option::VALUE_NONE, 'Convert the configuration to the PHP format.'), new Input_Option('--config-reference', null, Input_Option::VALUE_NONE, 'Display the configuration reference.'), new Input_Option('--debug', null, Input_Option::VALUE_NONE, 'Provide debugging information about current environment.'), new Input_Option('--version', '-V', Input_Option::VALUE_NONE, 'Display version.'), new Input_Option('--no-interaction', '-n', Input_Option::VALUE_NONE, 'Do not ask any interactive question.'), new Input_Option('--colors', null, Input_Option::VALUE_NONE, 'Force ANSI color in the output. By default color support is' . PHP_EOL . 'guessed based on your platform and the output if not specified.'), new Input_Option('--no-colors', null, Input_Option::VALUE_NONE, 'Force no ANSI color in the output.'), new Input_Option('--xdebug', null, Input_Option::VALUE_NONE, 'Allow Xdebug to run.')]);
    }
    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function do_run(Input_Interface $input, Output_Interface $output): int
    {
        // Register deprecation collector as early as possible
        Deprecation_Collector::get_instance()->register();
        $is_xdebug_allowed = $input->has_parameter_option('--xdebug') || extension_loaded('xdebug') && xdebug_is_debugger_active();
        if (!$is_xdebug_allowed) {
            $xdebug_handler = new Xdebug_Handler('behat');
            $xdebug_handler->set_persistent();
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
        if ($input->has_parameter_option(['--config-reference'])) {
            $input = new Array_Input(['--config-reference' => true]);
        }
        if ($path = $input->get_parameter_option(['--config', '-c'])) {
            if (!is_file($path)) {
                throw new Configuration_Loading_Exception('The requested config file does not exist');
            }
            $this->configuration_loader->set_configuration_file_path($path);
        }
        $command = $this->create_command($input, $output);
        $this->add_commands([$command]);
        return parent::do_run($input, $output);
    }
    protected function get_default_commands(): array
    {
        $commands = parent::get_default_commands();
        $commands[] = new Dump_Reference_Command($this->extension_manager);
        $commands[] = new Debug_Command($this, $this->configuration_loader, $this->extension_manager);
        $commands[] = new Convert_Config_Command($this->configuration_loader);
        return $commands;
    }
    /**
     * Configures container based on provided config file and profile.
     */
    private function load_configuration(Input_Interface $input): array
    {
        $profile = $input->get_parameter_option(['--profile', '-p']) ?: 'default';
        return $this->configuration_loader->load_configuration($profile);
    }
    /**
     * Creates main command for application.
     */
    private function create_command(Input_Interface $input, Output_Interface $output): Symfony_Command
    {
        return $this->create_container($input, $output)->get('cli.command');
    }
    /**
     * Creates container instance, loads extensions and freezes it.
     */
    private function create_container(Input_Interface $input, Output_Interface $output): Container_Builder
    {
        $base_path = rtrim($this->get_base_path(), DIRECTORY_SEPARATOR);
        $container = new Container_Builder();
        $container->set_parameter('cli.command.name', $this->get_name());
        $container->set_parameter('paths.base', $base_path);
        $container->set('cli.input', $input);
        $container->set('cli.output', $output);
        $extension = new Container_Loader($this->extension_manager);
        $extension->load($container, $this->load_configuration($input));
        $container->add_object_resource($extension);
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
    protected function get_command_name(Input_Interface $input): string
    {
        if ($input->has_parameter_option(['--config-reference'])) {
            return 'dump-reference';
        }
        if ($input->has_parameter_option(['--debug'])) {
            return 'debug';
        }
        if ($input->has_parameter_option(['--convert-config'])) {
            return 'convert-config';
        }
        return $this->get_name();
    }
    protected function configure_io(Input_Interface $input, Output_Interface $output): void
    {
        if ($input->has_parameter_option(['--colors'])) {
            $output->set_decorated(true);
        } elseif ($input->has_parameter_option(['--no-colors'])) {
            $output->set_decorated(false);
        }
        parent::configure_io($input, $output);
    }
}