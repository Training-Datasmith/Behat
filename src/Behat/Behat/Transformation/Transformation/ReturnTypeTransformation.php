<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Transformation;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Transformation\Call\Transformation_Call;
use Behat\Behat\Transformation\Simple_Argument_Transformation;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Runtime_Callee;
use Closure;
use ReflectionClass;
use Reflection_Exception;
use Reflection_Function_Abstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Stringable;
/**
 * By-type object transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Return_Type_Transformation extends Runtime_Callee implements Stringable, Simple_Argument_Transformation
{
    public static function supports_pattern_and_method($pattern, ReflectionMethod $method)
    {
        $return_class = self::get_return_class($method);
        if (null === $return_class) {
            return false;
        }
        return '' === $pattern;
    }
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct($pattern, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_argument_value)
    {
        $return_class = self::get_return_class($this->get_reflection());
        if (null === $return_class) {
            return false;
        }
        $parameter_class = $this->get_parameter_class_name_by_index($definition_call, $argument_index);
        return $parameter_class === $return_class;
    }
    public function transform_argument(Call_Center $call_center, Definition_Call $definition_call, $argument_index, $argument_value)
    {
        $call = new Transformation_Call($definition_call->get_environment(), $definition_call->get_callee(), $this, [$argument_value]);
        $result = $call_center->make_call($call);
        if ($result->has_exception()) {
            throw $result->get_exception();
        }
        return $result->get_return();
    }
    public function get_priority(): int
    {
        return 80;
    }
    public function get_pattern(): string
    {
        return '';
    }
    public function __toString(): string
    {
        return 'ReturnTypeTransform';
    }
    /**
     * Extracts parameters from provided definition call.
     */
    private static function get_return_class(Reflection_Function_Abstract $reflection): ?string
    {
        $type = $reflection->get_return_type();
        // Skip ReflectionUnionType as they can't be relied on for a transform
        if (null === $type || !$type instanceof ReflectionNamedType || $type->is_builtin()) {
            return null;
        }
        return $type->get_name();
    }
    /**
     * Attempts to get definition parameter using its index (parameter position or name).
     *
     * @param string|int $argumentIndex
     *
     * @return string|null
     */
    private function get_parameter_class_name_by_index(Definition_Call $definition_call, $argument_index)
    {
        $parameters = array_filter(array_filter($this->get_call_parameters($definition_call), $this->has_index($argument_index)), $this->get_class_reflection());
        if (count($parameters) == 0) {
            return null;
        }
        return $this->get_class_reflection()(current($parameters))->get_name();
    }
    /**
     * Extracts parameters from provided definition call.
     *
     * @return ReflectionParameter[]
     */
    private function get_call_parameters(Definition_Call $definition_call): array
    {
        return $definition_call->get_callee()->get_reflection()->get_parameters();
    }
    /**
     * Returns appropriate closure for filtering parameter by index.
     *
     * @param string|int $index
     *
     * @return Closure
     */
    private function has_index($index)
    {
        return is_string($index) ? $this->has_name($index) : $this->has_position($index);
    }
    /**
     * Returns closure to filter parameter by name.
     *
     *
     * @return Closure
     */
    private function has_name(string $index)
    {
        return fn(ReflectionParameter $parameter): bool => $index === $parameter->get_name();
    }
    /**
     * Returns closure to filter parameter by position.
     *
     * @param int $index
     *
     * @return Closure
     */
    private function has_position($index)
    {
        return fn(ReflectionParameter $parameter): bool => $index === $parameter->get_position();
    }
    /**
     * Returns closure to filter parameter by typehinted class.
     */
    private function get_class_reflection(): Closure
    {
        return function (ReflectionParameter $parameter): ?ReflectionClass {
            $t = $parameter->get_type();
            if ($t instanceof ReflectionNamedType) {
                try {
                    return new ReflectionClass($t->get_name());
                } catch (Reflection_Exception) {
                    return null;
                }
            }
            return null;
        };
    }
}