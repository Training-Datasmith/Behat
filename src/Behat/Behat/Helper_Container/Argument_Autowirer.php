<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Helper_Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
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
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Autowires given arguments using provided container.
     *
     * @throws ContainerExceptionInterface if unset argument typehint can not be resolved from container
     */
    public function autowire_arguments(ReflectionFunctionAbstract $reflection, array $arguments): array
    {
        $new_arguments = $arguments;
        foreach ($reflection->getParameters() as $index => $parameter) {
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
        if (isset($arguments[$parameter->getName()]) || array_key_exists($parameter->getName(), $arguments)) {
            return false;
        }

        return (bool) $this->get_class_from_parameter($parameter);
    }

    private function get_class_from_parameter(ReflectionParameter $parameter): ?string
    {
        if (!($type = $parameter->getType()) || !$type instanceof ReflectionNamedType) {
            return null;
        }
        try {
            $type_string = $type->getName();
            if ($type_string == 'self') {
                return $parameter->getDeclaringClass()->getName();
            }
            if ($type_string == 'parent') {
                return $parameter->getDeclaringClass()->getParentClass()->getName();
            }
            // will throw if not valid class
            new ReflectionClass($type_string);

            return $type_string;
        } catch (ReflectionException) {
            return null;
        }
    }
}
