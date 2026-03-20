<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Environment\Handler;

use Behat\Behat\Context\Argument\Argument_Resolver_Factory;
use Behat\Behat\Context\Argument\Null_Factory;
use Behat\Behat\Context\Argument\Suite_Scoped_Resolver_Factory;
use Behat\Behat\Context\Argument\Suite_Scoped_Resolver_Factory_Adapter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Context_Class\Class_Resolver;
use Behat\Behat\Context\Context_Factory;
use Behat\Behat\Context\Environment\Initialized_Context_Environment;
use Behat\Behat\Context\Environment\Uninitialized_Context_Environment;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Exception\Environment_Isolation_Exception;
use Behat\Testwork\Environment\Handler\Environment_Handler;
use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Suite;
/**
 * Handles build and initialisation of the context-based environments.
 *
 * @see ContextFactory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Environment_Handler implements Environment_Handler
{
    private readonly Argument_Resolver_Factory $resolver_factory;
    /**
     * @var ClassResolver[]
     */
    private array $class_resolvers = [];
    /**
     * Initializes handler.
     *
     * @param ArgumentResolverFactory|SuiteScopedResolverFactory $resolverFactory
     */
    public function __construct(private readonly Context_Factory $context_factory, $resolver_factory = null)
    {
        if ($resolver_factory && !$resolver_factory instanceof Argument_Resolver_Factory) {
            $resolver_factory = new Suite_Scoped_Resolver_Factory_Adapter($resolver_factory);
        }
        $this->resolver_factory = $resolver_factory ?: new Null_Factory();
    }
    /**
     * Registers context class resolver.
     */
    public function register_class_resolver(Class_Resolver $resolver): void
    {
        $this->class_resolvers[] = $resolver;
    }
    public function supports_suite(Suite $suite)
    {
        return $suite->has_setting('contexts');
    }
    public function build_environment(Suite $suite): Environment
    {
        $environment = new Uninitialized_Context_Environment($suite);
        foreach ($this->get_normalized_context_settings($suite) as $context) {
            $environment->register_context_class($this->resolve_class($context[0]), $context[1]);
        }
        return $environment;
    }
    public function supports_environment_and_subject(Environment $environment, $test_subject = null): bool
    {
        return $environment instanceof Uninitialized_Context_Environment;
    }
    public function isolate_environment(Environment $environment, $test_subject = null): Initialized_Context_Environment
    {
        if (!$environment instanceof Uninitialized_Context_Environment) {
            throw new Environment_Isolation_Exception(sprintf('ContextEnvironmentHandler does not support isolation of `%s` environment.', $environment::class), $environment);
        }
        $initialised_environment = new Initialized_Context_Environment($environment->get_suite());
        $resolvers = $this->resolver_factory->create_argument_resolvers($initialised_environment);
        foreach ($environment->get_context_classes_with_arguments() as $class => $arguments) {
            $context = $this->context_factory->create_context($class, $arguments, $resolvers);
            $initialised_environment->register_context($context);
        }
        return $initialised_environment;
    }
    /**
     * Returns normalized suite context settings.
     */
    private function get_normalized_context_settings(Suite $suite): array
    {
        return array_map(function (array|string $context): array {
            $class = $context;
            $arguments = [];
            if (is_array($context)) {
                $class = current(array_keys($context));
                $arguments = $context[$class];
            }
            return [$class, $arguments];
        }, $this->get_suite_contexts($suite));
    }
    /**
     * Returns array of context classes configured for the provided suite.
     *
     * @return array<class-string<Context>|array<class-string<Context>,array>>
     *
     * @throws SuiteConfigurationException If `contexts` setting is not an array
     */
    private function get_suite_contexts(Suite $suite)
    {
        if (!is_array($suite->get_setting('contexts'))) {
            throw new Suite_Configuration_Exception(sprintf('`contexts` setting of the "%s" suite is expected to be an array, %s given.', $suite->get_name(), gettype($suite->get_setting('contexts'))), $suite->get_name());
        }
        return $suite->get_setting('contexts');
    }
    /**
     * Resolves class using registered class resolvers.
     *
     * @param string $class
     *
     * @return string
     */
    private function resolve_class($class)
    {
        foreach ($this->class_resolvers as $resolver) {
            if ($resolver->supports_class($class)) {
                return $resolver->resolve_class($class);
            }
        }
        return $class;
    }
}