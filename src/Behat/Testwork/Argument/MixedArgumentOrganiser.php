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

use Behat\Gherkin\Node\Py_String_Node;
use Behat\Gherkin\Node\Table_Node;
use Behat\Testwork\Argument\Exception\Unexpected_Multiline_Argument_Exception;
use ReflectionClass;
use Reflection_Exception;
use Reflection_Function_Abstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
/**
 * Organises function arguments using its reflection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Mixed_Argument_Organiser implements Argument_Organiser
{
    private array $defined_arguments = [];
    /**
     * Organises arguments using function reflection.
     *
     * @param mixed[]                    $arguments
     *
     * @return mixed[]
     */
    public function organise_arguments(Reflection_Function_Abstract $function, array $arguments): array
    {
        return $this->prepare_arguments($function, $arguments);
    }
    /**
     * Prepares arguments based on provided parameters.
     *
     * @param mixed[]               $arguments
     *
     * @return mixed[]
     */
    private function prepare_arguments(Reflection_Function_Abstract $function, array $arguments): array
    {
        $parameters = $function->get_parameters();
        $this->mark_all_arguments_undefined();
        [$named, $typehinted, $numbered] = $this->split_arguments($parameters, $arguments);
        $was_multiline_provided = $this->has_multiline_argument($numbered);
        $arguments = $this->prepare_named_arguments($parameters, $named) + $this->prepare_typehinted_arguments($parameters, $typehinted) + $this->prepare_numbered_arguments($parameters, $numbered) + $this->prepare_default_arguments($parameters);
        // If a parameter was a TableNode or a PystringNode, but has been removed from the final arguments,
        // then it's an error in the feature file.
        if ($was_multiline_provided && !$this->has_multiline_argument($arguments)) {
            throw new Unexpected_Multiline_Argument_Exception(sprintf('You have passed a TableNode or PystringNode, but it was not used by %s.', $function instanceof ReflectionMethod ? $function->class . '::' . $function->get_name() : $function->get_name()));
        }
        return $this->reorder_arguments($parameters, $arguments);
    }
    /**
     * @param array<mixed> $arguments
     */
    private function has_multiline_argument(array $arguments): bool
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof Table_Node || $argument instanceof Py_String_Node) {
                return true;
            }
        }
        return false;
    }
    /**
     * Splits arguments into three separate arrays - named, numbered and typehinted.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @return array{array<string, mixed>, list<mixed>, list<mixed>}
     */
    private function split_arguments(array $parameters, array $arguments): array
    {
        $parameter_names = array_map(fn(ReflectionParameter $parameter): string => $parameter->get_name(), $parameters);
        $named_arguments = [];
        $numbered_arguments = [];
        $typehinted_arguments = [];
        foreach ($arguments as $key => $val) {
            if ($this->is_string_key_and_exists_in_parameters($key, $parameter_names)) {
                $named_arguments[$key] = $val;
            } elseif ($this->is_parameter_typehinted_in_argument_list($parameters, $val)) {
                $typehinted_arguments[] = $val;
            } else {
                $numbered_arguments[] = $val;
            }
        }
        return [$named_arguments, $typehinted_arguments, $numbered_arguments];
    }
    /**
     * Checks that provided argument key is a string and it matches some parameter name.
     *
     * @param string[] $parameterNames
     */
    private function is_string_key_and_exists_in_parameters(int|string $argument_key, array $parameter_names): bool
    {
        return is_string($argument_key) && in_array($argument_key, $parameter_names);
    }
    /**
     * Check if a given value is typehinted in the argument list.
     *
     * @param  ReflectionParameter[] $parameters
     */
    private function is_parameter_typehinted_in_argument_list(array $parameters, $value): bool
    {
        if (!is_object($value)) {
            return false;
        }
        foreach ($parameters as $parameter) {
            if ($this->is_value_matches_typehinted_parameter($value, $parameter)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if value matches typehint of provided parameter.
     */
    private function is_value_matches_typehinted_parameter(object $value, ReflectionParameter $parameter): bool
    {
        foreach ($this->get_reflection_classes_from_parameter($parameter) as $typehint_refl) {
            if ($typehint_refl->is_instance($value)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Captures argument values based on their respective names.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $namedArguments
     *
     * @return mixed[]
     */
    private function prepare_named_arguments(array $parameters, array $named_arguments): array
    {
        $arguments = [];
        foreach ($parameters as $num => $parameter) {
            $name = $parameter->get_name();
            if (array_key_exists($name, $named_arguments)) {
                $arguments[$name] = $named_arguments[$name];
                $this->mark_argument_defined($num);
            }
        }
        return $arguments;
    }
    /**
     * Captures argument values for typehinted arguments based on the given candidates.
     *
     * This method attempts to match up the best fitting arguments to each constructor argument.
     *
     * This case specifically fixes the issue where a constructor asks for a parent and child class,
     * as separate arguments, but both arguments could satisfy the first argument,
     * so they would both be passed in (overwriting each other).
     *
     * This will ensure that the children (exact class matches) are mapped first, and then other dependencies
     * are mapped sequentially (to arguments which they are an `instanceof`).
     *
     * As such, this requires two passes of the $parameters array to ensure it is mapped as accurately as possible.
     *
     * @param ReflectionParameter[] $parameters          Reflection Parameters (constructor argument requirements)
     * @param mixed[]               $typehintedArguments Resolved arguments
     *
     * @return mixed[] Ordered list of arguments, index is the constructor argument position, value is what will be injected
     */
    private function prepare_typehinted_arguments(array $parameters, array $typehinted_arguments): array
    {
        $arguments = [];
        $candidates = $typehinted_arguments;
        $this->apply_predicate_to_typehinted_arguments($parameters, $candidates, $arguments, $this->class_matching_predicate_for_typehinted_arguments(...));
        // This iteration maps up everything else, providing the argument is an instanceof the parameter.
        $this->apply_predicate_to_typehinted_arguments($parameters, $candidates, $arguments, $this->is_instance_predicate_for_typehinted_arguments(...));
        return $arguments;
    }
    /**
     * Filtered out superfluous parameters for matching up typehinted arguments.
     *
     * @param  ReflectionParameter[] $parameters Constructor Arguments
     *
     * @return ReflectionParameter[]             Filtered $parameters
     */
    private function filter_applicable_typehinted_parameters(array $parameters): array
    {
        return array_filter($parameters, fn($parameter, $num): bool => !$this->is_argument_defined($num) && $this->get_reflection_classes_from_parameter($parameter), ARRAY_FILTER_USE_BOTH);
    }
    /**
     * @return ReflectionClass[]
     */
    private function get_reflection_classes_from_parameter(ReflectionParameter $parameter): array
    {
        $classes = [];
        if (!$parameter->has_type()) {
            return $classes;
        }
        $type = $parameter->get_type();
        if ($type instanceof ReflectionNamedType) {
            $types = [$type];
        } elseif ($type instanceof ReflectionUnionType) {
            $types = $type->get_types();
        } else {
            $types = [];
        }
        foreach ($types as $type) {
            // ReflectionUnionType::getTypes is only documented as returning ReflectionType[]
            if (!$type instanceof ReflectionNamedType) {
                continue;
            }
            $type_string = $type->get_name();
            if ($type_string == 'self') {
                $type_string = $parameter->get_declaring_class();
            } elseif ($type_string == 'parent') {
                $type_string = $parameter->get_declaring_class()->get_parent_class();
            }
            try {
                $classes[] = new ReflectionClass($type_string);
            } catch (Reflection_Exception) {
                continue;
            }
        }
        return $classes;
    }
    /**
     * Applies a predicate for each candidate when matching up typehinted arguments.
     * This passes through to another loop of the candidates in @matchParameterToCandidateUsingPredicate,
     * because this method is "too complex" with two loops...
     *
     * @param  ReflectionParameter[] $parameters Reflection Parameters (constructor argument requirements)
     * @param  mixed[]               &$candidates Resolved arguments
     * @param  mixed[]               &$arguments  Argument mapping
     * @param  callable              $predicate   Callable predicate to apply to each candidate
     */
    private function apply_predicate_to_typehinted_arguments(array $parameters, array &$candidates, array &$arguments, \Closure $predicate): void
    {
        $filtered = $this->filter_applicable_typehinted_parameters($parameters);
        foreach ($filtered as $parameter) {
            $this->match_parameter_to_candidate_using_predicate($parameter, $candidates, $arguments, $predicate);
        }
    }
    /**
     * Applies a predicate for each candidate when matching up typehinted arguments.
     * This helps to avoid repetition when looping them, as multiple passes are needed over the parameters / candidates.
     *
     * @param  ReflectionParameter $parameter   Reflection Parameter (constructor argument requirements)
     * @param  mixed[]             &$candidates Resolved arguments
     * @param  mixed[]             &$arguments  Argument mapping
     * @param  callable            $predicate   Callable predicate to apply to each candidate
     *
     * @return bool Returns true if a candidate has been matched to the given parameter, otherwise false
     */
    public function match_parameter_to_candidate_using_predicate(ReflectionParameter $parameter, array &$candidates, array &$arguments, $predicate): bool
    {
        foreach ($candidates as $candidate_index => $candidate) {
            foreach ($this->get_reflection_classes_from_parameter($parameter) as $class) {
                if ($predicate($class, $candidate)) {
                    $num = $parameter->get_position();
                    $arguments[$num] = $candidate;
                    $this->mark_argument_defined($num);
                    unset($candidates[$candidate_index]);
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Typehinted argument predicate to check if the argument and parameter classes match equally.
     *
     * @param  ReflectionClass $reflectionClass Typehinted argument
     * @param  mixed           $candidate       Resolved argument
     */
    private function class_matching_predicate_for_typehinted_arguments(ReflectionClass $reflection_class, $candidate): bool
    {
        return $reflection_class->get_name() === $candidate::class;
    }
    /**
     * Typehinted argument predicate to check if the argument is an instance of the parameter.
     *
     * @param  ReflectionClass $reflectionClass Typehinted argument
     * @param  mixed           $candidate       Resolved argument
     */
    private function is_instance_predicate_for_typehinted_arguments(ReflectionClass $reflection_class, $candidate): bool
    {
        return $reflection_class->is_instance($candidate);
    }
    /**
     * Captures argument values for undefined arguments based on their respective numbers.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $numberedArguments
     *
     * @return mixed[]
     */
    private function prepare_numbered_arguments(array $parameters, array $numbered_arguments): array
    {
        $arguments = [];
        $increment = 0;
        foreach (array_keys($parameters) as $num) {
            if ($this->is_argument_defined($num)) {
                continue;
            }
            if (array_key_exists($increment, $numbered_arguments)) {
                $arguments[$num] = $numbered_arguments[$increment++];
                $this->mark_argument_defined($num);
            }
        }
        return $arguments;
    }
    /**
     * Captures argument values for undefined arguments based on parameters defaults.
     *
     * @param ReflectionParameter[] $parameters
     *
     * @return mixed[]
     */
    private function prepare_default_arguments(array $parameters): array
    {
        $arguments = [];
        foreach ($parameters as $num => $parameter) {
            if ($this->is_argument_defined($num)) {
                continue;
            }
            if ($parameter->is_default_value_available()) {
                $arguments[$num] = $parameter->get_default_value();
                $this->mark_argument_defined($num);
            }
        }
        return $arguments;
    }
    /**
     * Reorders arguments based on their respective parameters order.
     *
     * @param ReflectionParameter[] $parameters
     *
     * @return mixed[]
     */
    private function reorder_arguments(array $parameters, array $arguments): array
    {
        $ordered_arguments = [];
        foreach ($parameters as $num => $parameter) {
            $name = $parameter->get_name();
            if (array_key_exists($num, $arguments)) {
                $ordered_arguments[$num] = $arguments[$num];
            } elseif (array_key_exists($name, $arguments)) {
                $ordered_arguments[$name] = $arguments[$name];
            }
        }
        return $ordered_arguments;
    }
    /**
     * Marks arguments at all positions as undefined.
     *
     * This is used to share state between get*Arguments() methods.
     */
    private function mark_all_arguments_undefined(): void
    {
        $this->defined_arguments = [];
    }
    /**
     * Marks an argument at provided position as defined.
     *
     * @param int $position
     */
    private function mark_argument_defined($position): void
    {
        $this->defined_arguments[$position] = true;
    }
    /**
     * Checks if an argument at provided position is defined.
     *
     * @param int $position
     */
    private function is_argument_defined($position): bool
    {
        return isset($this->defined_arguments[$position]);
    }
}