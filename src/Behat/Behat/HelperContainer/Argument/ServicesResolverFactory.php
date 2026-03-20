<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container\Argument;

use Behat\Behat\Context\Argument\Argument_Resolver;
use Behat\Behat\Context\Argument\Argument_Resolver_Factory;
use Behat\Behat\Context\Argument\Suite_Scoped_Resolver_Factory;
use Behat\Behat\Helper_Container\Built_In_Service_Container;
use Behat\Behat\Helper_Container\Environment\Service_Container_Environment;
use Behat\Behat\Helper_Container\Exception\Wrong_Container_Class_Exception;
use Behat\Behat\Helper_Container\Exception\Wrong_Services_Configuration_Exception;
use Behat\Behat\Helper_Container\Service_Container\Helper_Container_Extension;
use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Psr\Container\Container_Interface;
use Symfony\Component\Dependency_Injection\Tagged_Container_Interface;
/**
 * Generates ServiceContainer argument resolvers based on suite's `services` setting.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Services_Resolver_Factory implements Suite_Scoped_Resolver_Factory, Argument_Resolver_Factory
{
    /**
     * Initialises factory.
     */
    public function __construct(private Tagged_Container_Interface $container)
    {
    }
    /**
     * @deprecated as part of SuiteScopedResolverFactory deprecation. Would be removed in 4.0
     *
     * @throws WrongServicesConfigurationException
     * @throws WrongContainerClassException
     */
    public function generate_argument_resolvers(Suite $suite): array
    {
        Deprecation_Collector::trigger('SuiteScopedResolverFactory::generateArgumentResolvers() was deprecated and will be removed in 4.0');
        if (!$suite->has_setting('services')) {
            return [];
        }
        $container = $this->create_container($suite->get_setting('services'));
        return $this->create_resolvers($container, false);
    }
    /**
     * @throws WrongServicesConfigurationException
     * @throws WrongContainerClassException
     */
    public function create_argument_resolvers(Environment $environment): array
    {
        $suite = $environment->get_suite();
        if (!$suite->has_setting('services')) {
            return [];
        }
        $container = $this->create_container($suite->get_setting('services'));
        $autowire = $suite->has_setting('autowire') && $suite->get_setting('autowire');
        if ($environment instanceof Service_Container_Environment) {
            $environment->set_service_container($container);
        }
        return $this->create_resolvers($container, $autowire);
    }
    /**
     * Creates container from the setting passed.
     *
     * @throws WrongServicesConfigurationException
     */
    private function create_container($settings)
    {
        if (is_string($settings)) {
            return $this->create_container_from_string($settings);
        }
        if (is_array($settings)) {
            return $this->create_container_from_array($settings);
        }
        throw new Wrong_Services_Configuration_Exception(sprintf('`services` must be either string or an array, but `%s` given.', gettype($settings)));
    }
    /**
     * Creates custom container using class/constructor given.
     *
     *
     * @throws WrongServicesConfigurationException
     */
    private function create_container_from_string(string $settings)
    {
        if (0 === mb_strpos($settings, '@')) {
            return $this->load_container_from_container(mb_substr($settings, 1));
        }
        return $this->create_container_from_class_spec($settings);
    }
    /**
     * Creates built-in service container with provided settings.
     */
    private function create_container_from_array(array $settings): Built_In_Service_Container
    {
        return new Built_In_Service_Container($settings);
    }
    /**
     * Loads container from string.
     *
     *
     * @throws WrongServicesConfigurationException
     */
    private function load_container_from_container(string $name): object
    {
        $services = $this->container->find_tagged_service_ids(Helper_Container_Extension::HELPER_CONTAINER_TAG);
        if (!array_key_exists($name, $services)) {
            throw new Wrong_Services_Configuration_Exception(sprintf('Service container `@%s` was not found.', $name));
        }
        return $this->container->get($name);
    }
    /**
     * Creates container from string-based class spec.
     *
     * @param string $classSpec
     */
    private function create_container_from_class_spec($class_spec)
    {
        $constructor = explode('::', $class_spec);
        if (2 === count($constructor)) {
            return call_user_func($constructor);
        }
        return new $constructor[0]();
    }
    /**
     * Checks if container implements the correct interface and creates resolver using it.
     *
     *
     * @return list<ArgumentResolver>
     * @throws WrongContainerClassException
     */
    private function create_resolvers($container, bool $autowire): array
    {
        if (!$container instanceof Container_Interface) {
            throw new Wrong_Container_Class_Exception(sprintf('Service container is expected to implement `Psr\Container\ContainerInterface`, but `%s` does not.', $container::class), $container::class);
        }
        if ($autowire) {
            return [new Services_Resolver($container), new Autowiring_Resolver($container)];
        }
        return [new Services_Resolver($container)];
    }
}