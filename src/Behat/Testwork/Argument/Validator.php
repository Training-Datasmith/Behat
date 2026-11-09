<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\Unknown_Parameter_Value_Exception;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Validates function arguments.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Validator
{
    /**
     * Validates that all arguments are in place, throws exception otherwise.
     *
     * @param mixed[]                    $arguments
     *
     * @throws Unknown_Parameter_Value_Exception
     */
    public function validate_arguments(ReflectionFunctionAbstract $function, array $arguments): void
    {
        foreach ($function->getParameters() as $num => $parameter) {
            $this->validate_argument($parameter, $num, $arguments);
        }
    }

    /**
     * Validates given argument.
     */
    private function validate_argument(ReflectionParameter $parameter, $parameter_index, array $given_arguments): void
    {
        if ($parameter->isDefaultValueAvailable()) {
            return;
        }
        if (array_key_exists($parameter_index, $given_arguments)) {
            return;
        }
        if (array_key_exists($parameter->getName(), $given_arguments)) {
            return;
        }
        throw new Unknown_Parameter_Value_Exception(sprintf('Can not find a matching value for an argument `$%s` of the method `%s`.', $parameter->getName(), $this->get_function_path($parameter->getDeclaringFunction())));
    }

    /**
     * Returns function path for a provided reflection.
     */
    private function get_function_path(ReflectionFunctionAbstract $function): string
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $function->getDeclaringClass()->getName(), $function->getName());
        }

        return sprintf('%s()', $function->getName());
    }
}
