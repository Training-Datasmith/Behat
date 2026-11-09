<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Exception\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Exception\Cli\Verbosity_Controller;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Exception\Stringer\Php_Unit_Exception_Stringer;
use Behat\Testwork\Exception\Stringer\Testwork_Exception_Stringer;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides exception handling services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Exception_Extension implements Extension
{
    /*
     * Available services
     */
    public const PRESENTER_ID = 'exception.presenter';
    /*
     * Available extension points
     */
    public const STRINGER_TAG = 'exception.stringer';
    private Service_Processor $processor;
    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    public function get_config_key(): string
    {
        return 'exceptions';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $builder->add_defaults_if_not_set()->children()->scalar_node('verbosity')->info('Output verbosity')->example(sprintf('%d, %d, %d, %d', Output_Printer::VERBOSITY_NORMAL, Output_Printer::VERBOSITY_VERBOSE, Output_Printer::VERBOSITY_VERY_VERBOSE, Output_Printer::VERBOSITY_DEBUG))->default_value(Output_Printer::VERBOSITY_NORMAL);
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_presenter($container, $config['verbosity']);
        $this->load_default_stringers($container);
        $this->load_verbosity_controller($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_stringers($container);
    }
    /**
     * Loads exception presenter.
     *
     * @param int $verbosity
     */
    private function load_presenter(Container_Builder $container, $verbosity): void
    {
        $definition = new Definition(Exception_Presenter::class, ['%paths.base%', $verbosity, new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->set_definition(self::PRESENTER_ID, $definition);
    }
    /**
     * Loads default stringer.
     */
    private function load_default_stringers(Container_Builder $container): void
    {
        $definition = new Definition(Php_Unit_Exception_Stringer::class);
        $definition->add_tag(self::STRINGER_TAG, ['priority' => 50]);
        $container->set_definition(self::STRINGER_TAG . '.phpunit', $definition);
        $definition = new Definition(Testwork_Exception_Stringer::class);
        $definition->add_tag(self::STRINGER_TAG, ['priority' => 50]);
        $container->set_definition(self::STRINGER_TAG . '.testwork', $definition);
    }
    /**
     * Processes all available exception stringers.
     */
    private function process_stringers(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::STRINGER_TAG);
        $definition = $container->get_definition(self::PRESENTER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerExceptionStringer', [$reference]);
        }
    }
    /**
     * Loads verbosity controller.
     *
     * @param ContainerBuilder $container
     */
    private function load_verbosity_controller($container): void
    {
        $definition = new Definition(Verbosity_Controller::class, [new Reference(self::PRESENTER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.exception_verbosity', $definition);
    }
}