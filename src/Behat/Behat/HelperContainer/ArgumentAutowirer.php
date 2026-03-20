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

use Psr\Container\Container_Exception_Interface;
use Psr\Container\Container_Interface as PsrContainerInterface;
use ReflectionClass;
use Reflection_Exception;
use Reflection_Function_Abstract;
use ReflectionNamedType;
use ReflectionParameter;
/**
 * Automatically wires arguments of a given function from inside the container by using type-hints.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Argument_Autowirer
{
    /**
     * Initialises wirer.
     */
    public function __construct(private Psr_Container_Interface $container)
    {
    }
    /**
     * Autowires given arguments using provided container.
     *
     * @throws ContainerExceptionInterface if unset argument typehint can not be resolved from container
     */
    public function autowire_arguments(Reflection_Function_Abstract $reflection, array $arguments): array
    {
        $new_arguments = $arguments;
        foreach ($reflection->get_parameters() as $index => $parameter) {
            if ($this->is_argument_wireable($new_arguments, $index, $parameter)) {
                $new_arguments[$index] = $this->container->get($this->get_class_from_parameter($parameter));
            }
        }
        return $new_arguments;
    }
    /**
     * Checks if given argument is wireable.
     *
     * Argument is wireable if it was not previously set and it has a class type-hint.
     *
     * @param int $index
     *
     * @return bool
     */
    private function is_argument_wireable(array $arguments, $index, ReflectionParameter $parameter)
    {
        if (isset($arguments[$index]) || array_key_exists($index, $arguments)) {
            return false;
        }
        if (isset($arguments[$parameter->get_name()]) || array_key_exists($parameter->get_name(), $arguments)) {
            return false;
        }
        return (bool) $this->get_class_from_parameter($parameter);
    }
    private function get_class_from_parameter(ReflectionParameter $parameter): ?string
    {
        if (!($type = $parameter->get_type()) || !$type instanceof ReflectionNamedType) {
            return null;
        }
        try {
            $type_string = $type->get_name();
            if ($type_string == 'self') {
                return $parameter->get_declaring_class()->get_name();
            }
            if ($type_string == 'parent') {
                return $parameter->get_declaring_class()->get_parent_class()->get_name();
            }
            // will throw if not valid class
            new ReflectionClass($type_string);
            return $type_string;
        } catch (Reflection_Exception) {
            return null;
        }
    }
}