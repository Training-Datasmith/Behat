<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Service_Container;

use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Tester\Cli\Rerun_Controller;
use Behat\Behat\Tester\Exception\Stringer\Pending_Exception_Stringer;
use Behat\Behat\Tester\Runtime\Isolating_Scenario_Tester;
use Behat\Behat\Tester\Runtime\Runtime_Background_Tester;
use Behat\Behat\Tester\Runtime\Runtime_Feature_Tester;
use Behat\Behat\Tester\Runtime\Runtime_Outline_Tester;
use Behat\Behat\Tester\Runtime\Runtime_Scenario_Tester;
use Behat\Behat\Tester\Runtime\Runtime_Step_Tester;
use Behat\Behat\Tester\Step_Container_Tester;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Tester\Service_Container\Tester_Extension as BaseExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides gherkin testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Tester_Extension extends BaseExtension
{
    /*
     * Available services
     */
    public const SCENARIO_TESTER_ID = 'tester.scenario';
    public const OUTLINE_TESTER_ID = 'tester.outline';
    public const EXAMPLE_TESTER_ID = 'tester.example';
    public const BACKGROUND_TESTER_ID = 'tester.background';
    public const STEP_TESTER_ID = 'tester.step';
    /**
     * Available extension points.
     */
    public const SCENARIO_TESTER_WRAPPER_TAG = 'tester.scenario.wrapper';
    public const OUTLINE_TESTER_WRAPPER_TAG = 'tester.outline.wrapper';
    public const EXAMPLE_TESTER_WRAPPER_TAG = 'tester.example.wrapper';
    public const BACKGROUND_TESTER_WRAPPER_TAG = 'tester.background.wrapper';
    public const STEP_TESTER_WRAPPER_TAG = 'tester.step.wrapper';
    private readonly Service_Processor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
        parent::__construct($this->processor);
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        parent::configure($builder);
        $builder->children()->scalarNode('rerun_cache')->info('Sets the rerun cache path')->defaultValue(is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat_rerun_cache' : null);
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        parent::load($container, $config);
        $this->load_rerun_controller($container, $config['rerun_cache']);
        $this->load_pending_exception_stringer($container);
    }

    public function process(ContainerBuilder $container): void
    {
        parent::process($container);
        $this->process_scenario_tester_wrappers($container);
        $this->process_outline_tester_wrappers($container);
        $this->process_example_tester_wrappers($container);
        $this->process_background_tester_wrappers($container);
        $this->process_step_tester_wrappers($container);
    }

    /**
     * Loads specification tester.
     */
    protected function load_specification_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Runtime_Feature_Tester::class, [new Reference(self::SCENARIO_TESTER_ID), new Reference(self::OUTLINE_TESTER_ID), new Reference(Environment_Extension::MANAGER_ID)]);
        $container->setDefinition(self::SPECIFICATION_TESTER_ID, $definition);
        $this->load_scenario_tester($container);
        $this->load_outline_tester($container);
        $this->load_background_tester($container);
        $this->load_step_tester($container);
    }

    /**
     * Loads scenario tester.
     */
    protected function load_scenario_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Step_Container_Tester::class, [new Reference(self::STEP_TESTER_ID)]);
        $container->setDefinition('tester.step_container', $definition);
        $definition = new Definition(Runtime_Scenario_Tester::class, [new Reference('tester.step_container'), new Reference(self::BACKGROUND_TESTER_ID)]);
        $container->setDefinition(self::SCENARIO_TESTER_ID, $definition);
        // Proper isolation for scenarios
        $definition = new Definition(Isolating_Scenario_Tester::class, [new Reference(self::SCENARIO_TESTER_ID), new Reference(Environment_Extension::MANAGER_ID)]);
        $definition->addTag(self::SCENARIO_TESTER_WRAPPER_TAG, ['priority' => -999999]);
        $container->setDefinition(self::SCENARIO_TESTER_WRAPPER_TAG . '.isolating', $definition);
    }

    /**
     * Loads outline tester.
     */
    protected function load_outline_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Runtime_Outline_Tester::class, [new Reference(self::EXAMPLE_TESTER_ID)]);
        $container->setDefinition(self::OUTLINE_TESTER_ID, $definition);
        $this->load_example_tester($container);
    }

    /**
     * Loads example tester.
     */
    protected function load_example_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Step_Container_Tester::class, [new Reference(self::STEP_TESTER_ID)]);
        $container->setDefinition('tester.step_container', $definition);
        $definition = new Definition(Runtime_Scenario_Tester::class, [new Reference('tester.step_container'), new Reference(self::BACKGROUND_TESTER_ID)]);
        $container->setDefinition(self::EXAMPLE_TESTER_ID, $definition);
        // Proper isolation for examples
        $definition = new Definition(Isolating_Scenario_Tester::class, [new Reference(self::EXAMPLE_TESTER_ID), new Reference(Environment_Extension::MANAGER_ID)]);
        $definition->addTag(self::EXAMPLE_TESTER_WRAPPER_TAG, ['priority' => -999999]);
        $container->setDefinition(self::EXAMPLE_TESTER_WRAPPER_TAG . '.isolating', $definition);
    }

    /**
     * Loads background tester.
     */
    protected function load_background_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Step_Container_Tester::class, [new Reference(self::STEP_TESTER_ID)]);
        $container->setDefinition('tester.step_container', $definition);
        $definition = new Definition(Runtime_Background_Tester::class, [new Reference('tester.step_container')]);
        $container->setDefinition(self::BACKGROUND_TESTER_ID, $definition);
    }

    /**
     * Loads step tester.
     */
    protected function load_step_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Runtime_Step_Tester::class, [new Reference(Definition_Extension::FINDER_ID), new Reference(Call_Extension::CALL_CENTER_ID)]);
        $container->setDefinition(self::STEP_TESTER_ID, $definition);
    }

    /**
     * Loads rerun controller.
     */
    protected function load_rerun_controller(ContainerBuilder $container, $cache_path)
    {
        $definition = new Definition(Rerun_Controller::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID), new Reference(Tester_Extension::RESULT_INTERPRETER_ID), $container->getParameter('paths.base'), $cache_path]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 200]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.rerun', $definition);
    }

    /**
     * Loads pending exception stringer.
     */
    protected function load_pending_exception_stringer(ContainerBuilder $container)
    {
        $definition = new Definition(Pending_Exception_Stringer::class);
        $definition->addTag(Exception_Extension::STRINGER_TAG);
        $container->setDefinition(Exception_Extension::STRINGER_TAG . '.pending', $definition);
    }

    /**
     * Processes all registered scenario tester wrappers.
     */
    protected function process_scenario_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::SCENARIO_TESTER_ID, self::SCENARIO_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered outline tester wrappers.
     */
    protected function process_outline_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::OUTLINE_TESTER_ID, self::OUTLINE_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered example tester wrappers.
     */
    protected function process_example_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::EXAMPLE_TESTER_ID, self::EXAMPLE_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered background tester wrappers.
     */
    protected function process_background_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::BACKGROUND_TESTER_ID, self::BACKGROUND_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered step tester wrappers.
     */
    protected function process_step_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::STEP_TESTER_ID, self::STEP_TESTER_WRAPPER_TAG);
    }
}
