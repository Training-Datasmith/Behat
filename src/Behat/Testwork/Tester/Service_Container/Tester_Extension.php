<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Specification\Service_Container\Specification_Extension;
use Behat\Testwork\Suite\Service_Container\Suite_Extension;
use Behat\Testwork\Tester\Cli\Exercise_Controller;
use Behat\Testwork\Tester\Cli\Strict_Controller;
use Behat\Testwork\Tester\Handler\Stop_On_Failure_Handler;
use Behat\Testwork\Tester\Result\Interpretation\Soft_Interpretation;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Behat\Testwork\Tester\Runtime\Runtime_Exercise;
use Behat\Testwork\Tester\Runtime\Runtime_Suite_Tester;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides tester services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Tester_Extension implements Extension
{
    /*
     * Available services
     */
    public const EXERCISE_ID = 'tester.exercise';
    public const SUITE_TESTER_ID = 'tester.suite';
    public const SPECIFICATION_TESTER_ID = 'tester.specification';
    public const RESULT_INTERPRETER_ID = 'tester.result.interpreter';
    public const STOP_ON_FAILURE_ID = 'tester.stop_on_failure';
    /**
     * Available extension points.
     */
    public const EXERCISE_WRAPPER_TAG = 'tester.exercise.wrapper';
    public const SUITE_TESTER_WRAPPER_TAG = 'tester.suite.wrapper';
    public const SPECIFICATION_TESTER_WRAPPER_TAG = 'tester.specification.wrapper';
    public const RESULT_INTERPRETATION_TAG = 'test.result.interpretation';
    private readonly Service_Processor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function get_config_key()
    {
        return 'testers';
    }

    public function initialize(Extension_Manager $extension_manager)
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $children_builder = $builder->addDefaultsIfNotSet()->children();
        $children_builder->scalarNode('stop_on_failure')->defaultValue(null);
        $children_builder->booleanNode('strict')->info('Sets the strict mode for result interpretation')->defaultFalse();
        $children_builder->booleanNode('skip')->info('Tells tester to skip all tests')->defaultFalse();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_exercise_controller($container, $config['skip']);
        $this->load_stop_on_failure_handler($container, $config['stop_on_failure']);
        $this->load_strict_controller($container, $config['strict']);
        $this->load_result_interpreter($container);
        $this->load_exercise($container);
        $this->load_suite_tester($container);
        $this->load_specification_tester($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_exercise_wrappers($container);
        $this->process_suite_tester_wrappers($container);
        $this->process_specification_tester_wrappers($container);
        $this->process_result_interpretations($container);
    }

    /**
     * Loads exercise cli controllers.
     *
     * @param bool          $skip
     */
    protected function load_exercise_controller(ContainerBuilder $container, $skip = false)
    {
        $definition = new Definition(Exercise_Controller::class, [new Reference(Suite_Extension::REGISTRY_ID), new Reference(Specification_Extension::FINDER_ID), new Reference(self::EXERCISE_ID), new Reference(self::RESULT_INTERPRETER_ID), $skip]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 0]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.exercise', $definition);
    }

    /**
     * Loads stop on failure handler.
     */
    private function load_stop_on_failure_handler(ContainerBuilder $container, ?bool $stop_on_failure): void
    {
        $definition = new Definition(Stop_On_Failure_Handler::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID), new Reference(Tester_Extension::RESULT_INTERPRETER_ID)]);
        if ($stop_on_failure === true) {
            $definition->addMethodCall('register_listeners');
        }
        $container->setDefinition(self::STOP_ON_FAILURE_ID, $definition);
    }

    /**
     * Loads exercise cli controllers.
     *
     * @param bool          $strict
     */
    protected function load_strict_controller(ContainerBuilder $container, $strict = false)
    {
        $definition = new Definition(Strict_Controller::class, [new Reference(self::RESULT_INTERPRETER_ID), $strict]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 300]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.strict', $definition);
    }

    /**
     * Loads result interpreter controller.
     */
    protected function load_result_interpreter(ContainerBuilder $container)
    {
        $definition = new Definition(Result_Interpreter::class);
        $container->setDefinition(self::RESULT_INTERPRETER_ID, $definition);
        $definition = new Definition(Soft_Interpretation::class);
        $definition->addTag(self::RESULT_INTERPRETATION_TAG);
        $container->setDefinition(self::RESULT_INTERPRETATION_TAG . '.soft', $definition);
    }

    /**
     * Loads exercise tester.
     */
    protected function load_exercise(ContainerBuilder $container)
    {
        $definition = new Definition(Runtime_Exercise::class, [new Reference(Environment_Extension::MANAGER_ID), new Reference(self::SUITE_TESTER_ID)]);
        $container->setDefinition(self::EXERCISE_ID, $definition);
    }

    /**
     * Loads suite tester.
     */
    protected function load_suite_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Runtime_Suite_Tester::class, [new Reference(self::SPECIFICATION_TESTER_ID)]);
        $container->setDefinition(self::SUITE_TESTER_ID, $definition);
    }

    /**
     * Loads specification tester.
     */
    abstract protected function load_specification_tester(ContainerBuilder $container);

    /**
     * Processes all registered exercise wrappers.
     */
    protected function process_exercise_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::EXERCISE_ID, self::EXERCISE_WRAPPER_TAG);
    }

    /**
     * Processes all registered suite tester wrappers.
     */
    protected function process_suite_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::SUITE_TESTER_ID, self::SUITE_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered specification tester wrappers.
     */
    protected function process_specification_tester_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::SPECIFICATION_TESTER_ID, self::SPECIFICATION_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered result interpretations.
     */
    protected function process_result_interpretations(ContainerBuilder $container)
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::RESULT_INTERPRETATION_TAG);
        $definition = $container->getDefinition(self::RESULT_INTERPRETER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_result_interpretation', [$reference]);
        }
    }
}
