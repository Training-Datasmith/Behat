<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\Unknown_Parameter_Value_Exception;
use Reflection_Function_Abstract;
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
     * @throws UnknownParameterValueException
     */
    public function validate_arguments(Reflection_Function_Abstract $function, array $arguments): void
    {
        foreach ($function->get_parameters() as $num => $parameter) {
            $this->validate_argument($parameter, $num, $arguments);
        }
    }
    /**
     * Validates given argument.
     *
     * @param int $parameterIndex
     */
    private function validate_argument(ReflectionParameter $parameter, $parameter_index, array $given_arguments): void
    {
        if ($parameter->is_default_value_available()) {
            return;
        }
        if (array_key_exists($parameter_index, $given_arguments)) {
            return;
        }
        if (array_key_exists($parameter->get_name(), $given_arguments)) {
            return;
        }
        throw new Unknown_Parameter_Value_Exception(sprintf('Can not find a matching value for an argument `$%s` of the method `%s`.', $parameter->get_name(), $this->get_function_path($parameter->get_declaring_function())));
    }
    /**
     * Returns function path for a provided reflection.
     */
    private function get_function_path(Reflection_Function_Abstract $function): string
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $function->get_declaring_class()->get_name(), $function->get_name());
        }
        return sprintf('%s()', $function->get_name());
    }
}