<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container;

use Behat\Behat\Helper_Container\Exception\Service_Not_Found_Exception;
use Behat\Behat\Helper_Container\Exception\Wrong_Services_Configuration_Exception;
use Psr\Container\Container_Interface as PsrContainerInterface;
use ReflectionClass;
use ReflectionMethod;
/**
 * Built-in service container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Built_In_Service_Container implements Psr_Container_Interface
{
    /**
     * @var array
     */
    private $instances;
    /**
     * Initialises container using provided service configuration.
     */
    public function __construct(private array $schema)
    {
    }
    public function has($id): bool
    {
        return array_key_exists($id, $this->schema);
    }
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new Service_Not_Found_Exception(sprintf('Service with id `%s` was not defined inside the `services` setting`.', $id), $id);
        }
        return $this->instances[$id] ??= $this->create_instance($id);
    }
    /**
     * Creates an instance of given service.
     *
     * @param string $id
     */
    private function create_instance($id)
    {
        $schema = $this->get_and_validate_service_schema($id);
        $reflection = new ReflectionClass($schema['class']);
        $arguments = $schema['arguments'];
        if ($factory_method = $this->get_and_validate_factory_method($reflection, $schema)) {
            return $factory_method->invoke_args(null, $arguments);
        }
        return $reflection->new_instance_args($arguments);
    }
    /**
     * Gets and validates a service configuration for a service with given ID.
     *
     *
     * @return array|string
     * @throws WrongServicesConfigurationException
     */
    private function get_and_validate_service_schema(string $id)
    {
        $schema = $this->schema[$id];
        if (null === $schema) {
            $schema = ['class' => $id];
        }
        if (is_string($schema)) {
            $schema = ['class' => $schema];
        }
        $schema['class'] = $this->get_and_validate_class($id, $schema);
        $schema['arguments'] = $this->get_and_validate_arguments($schema);
        return $schema;
    }
    /**
     * Gets and validates a class from schema.
     */
    private function get_and_validate_class(string $id, array $schema): string
    {
        if (!isset($schema['class'])) {
            $schema['class'] = $id;
        }
        return $schema['class'];
    }
    /**
     * Gets and validates arguments from schema.
     */
    private function get_and_validate_arguments(array $schema): array
    {
        return isset($schema['arguments']) ? (array) $schema['arguments'] : [];
    }
    /**
     * Gets and validates a factory method.
     *
     * @return ReflectionMethod|null
     */
    private function get_and_validate_factory_method(ReflectionClass $reflection, array $schema)
    {
        if (!isset($schema['factory_method'])) {
            return null;
        }
        $factory_method = $schema['factory_method'];
        $this->assert_factory_method_exists($reflection, $factory_method);
        $method = $reflection->get_method($factory_method);
        $this->assert_factory_method_is_static($method);
        return $method;
    }
    /**
     * Checks if factory method exists.
     *
     * @param string          $methodName
     *
     * @throws WrongServicesConfigurationException
     */
    private function assert_factory_method_exists(ReflectionClass $class, $method_name): void
    {
        if (!$class->has_method($method_name)) {
            throw new Wrong_Services_Configuration_Exception(sprintf('Factory method `%s::%s` does not exist.', $class->get_name(), $method_name));
        }
    }
    /**
     * Checks if factory method is static.
     *
     * @throws WrongServicesConfigurationException
     */
    private function assert_factory_method_is_static(ReflectionMethod $method): void
    {
        if (!$method->is_static()) {
            throw new Wrong_Services_Configuration_Exception(sprintf('Service factory methods must be static, but `%s::%s` is not.', $method->get_declaring_class()->get_name(), $method->get_name()));
        }
    }
}