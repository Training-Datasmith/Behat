<?php

declare (strict_types=1);
namespace Behat\Config\Converter;

use InvalidArgumentException;
use Php_Parser\Builder_Factory;
use Php_Parser\Node\Expr;
use Php_Parser\Node\Expr\Binary_Op\Bitwise_And;
use Php_Parser\Node\Expr\Binary_Op\Bitwise_Or;
use Php_Parser\Node\Expr\Bitwise_Not;
use Php_Parser\Node\Expr\New_;
use Php_Parser\Node\Name\Fully_Qualified;
use Php_Parser\Node\Stmt\Use_;
use ReflectionClass;
use Reflection_Class_Constant;
use ReflectionMethod;
class Config_Converter_Tools
{
    private static ?Builder_Factory $builder_factory = null;
    public static function add_method_call(string $class, string $method_name, array $arguments, Expr $expr): Expr
    {
        $builder_factory = self::get_builder_factory();
        $arguments = self::remove_default_arguments((new ReflectionClass($class))->get_method($method_name), $arguments);
        $args = $builder_factory->args(self::replace_class_references($arguments));
        return $builder_factory->method_call($expr, $method_name, $args);
    }
    private static function remove_default_arguments(ReflectionMethod $method, array $arguments): array
    {
        $has_removed_any = false;
        $result = [];
        foreach ($method->get_parameters() as $index => $parameter) {
            if ($parameter->is_variadic()) {
                // We can't do anything useful (easily) for methods with variadics, just return the originals
                return $arguments;
            }
            // Config objects can export their method arguments as positional or named arguments.
            $arg_key = array_key_exists($parameter->get_name(), $arguments) ? $parameter->get_name() : $index;
            // The Config object must provide a value for every argument (defaults are stripped here). If we are
            // missing arguments, that implies the ->toPhpExpr implementation is out of sync with the method definition.
            if (!array_key_exists($arg_key, $arguments)) {
                throw new InvalidArgumentException(sprintf('Missing argument #%d (%s) for %s::%s', $index, $parameter->get_name(), $method->get_declaring_class()->get_name(), $method->get_name()));
            }
            $arg_value = $arguments[$arg_key];
            if ($parameter->is_default_value_available() && $parameter->get_default_value() === $arg_value) {
                // We can skip this, it has the default value. That means we'll need to specify any following arguments
                // by name even if we were given them as positional.
                $has_removed_any = true;
            } else {
                $result[$has_removed_any ? $parameter->get_name() : $arg_key] = $arg_value;
            }
            // Record that this argument has been used
            unset($arguments[$arg_key]);
        }
        // We should have applied all arguments that were provided. If not, that implies the ->toPhpExpr implementation
        // is out of sync with the method definition.
        if ($arguments !== []) {
            throw new InvalidArgumentException(sprintf('Too many arguments provided for %s::%s (%s)', $method->get_declaring_class()->get_name(), $method->get_name(), implode(', ', array_keys($arguments))));
        }
        return $result;
    }
    public static function create_object(string $class_name): New_
    {
        $builder_factory = self::get_builder_factory();
        return $builder_factory->new(new Fully_Qualified($class_name));
    }
    public static function add_arguments_to_constructor(array $arguments, New_ $expr): void
    {
        $builder_factory = self::get_builder_factory();
        $expr->args = $builder_factory->args(self::replace_class_references($arguments));
    }
    public static function create_use_statement(string $class_name): Use_
    {
        $builder_factory = self::get_builder_factory();
        return $builder_factory->use($class_name)->get_node();
    }
    private static function get_builder_factory(): Builder_Factory
    {
        if (!self::$builder_factory instanceof Builder_Factory) {
            self::$builder_factory = new Builder_Factory();
        }
        return self::$builder_factory;
    }
    private static function replace_class_references(array $arguments): array
    {
        return array_map(static fn($arg) => is_string($arg) && class_exists($arg) ? self::get_class_const_reference($arg, 'class') : $arg, $arguments);
    }
    private static function get_class_const_reference(string $class_name, string $constant_name): Expr
    {
        $builder_factory = self::get_builder_factory();
        return $builder_factory->class_const_fetch($class_name, $constant_name);
    }
    public static function find_reference_to_class_constant(string $class_name, mixed $setting_value): mixed
    {
        $consts = (new ReflectionClass($class_name))->get_constants(Reflection_Class_Constant::IS_PUBLIC);
        foreach ($consts as $constant_name => $constant_value) {
            if ($setting_value === $constant_value) {
                return Config_Converter_Tools::get_class_const_reference($class_name, $constant_name);
            }
        }
        // Can't find a constant for it, just return the original value
        return $setting_value;
    }
    public static function error_reporting_to_constants(mixed $value): Expr
    {
        $builder_factory = self::get_builder_factory();
        if (!is_int($value)) {
            return $builder_factory->val($value);
        }
        // First remove the old E_STRICT value from both E_ALL and from the actual configured value.
        // This will make the behaviour consistent across all supported PHP versions. Since 8.4.0,
        // use of E_STRICT is deprecated and the value is no longer included in E_ALL. However,
        // the bit may still be set in the error_reporting integer in a user's YAML file.
        // Since 8.0, PHP cannot actually ever trigger an E_STRICT error, so the setting of the bit
        // is actually irrelevant and we can safely ignore it.
        $value &= ~2048;
        if ($value === (E_ALL & ~2048)) {
            // The value is equivalent to E_ALL (with or without the E_STRICT bit set, it doesn't matter).
            return $builder_factory->const_fetch('E_ALL');
        }
        if ($value === 0) {
            // error_reporting is disabled
            return $builder_factory->val(0);
        }
        // Split the value into constant expressions for the bit flags that are on and off.
        $flags_on = $flags_off = [];
        $remaining_value = $value;
        foreach (self::list_error_level_constants() as $const_name => $const_value) {
            $remaining_value &= ~$const_value;
            if (($value & $const_value) !== 0) {
                $flags_on[] = $builder_factory->const_fetch($const_name);
            } else {
                $flags_off[] = $builder_factory->const_fetch($const_name);
            }
        }
        if ($remaining_value !== 0) {
            // This is an edge case, their value includes a number that is not defined as a PHP error_reporting level
            // Keep it as a constant value so that the configuration doesn't change and the user can review it
            $flags_on[] = $builder_factory->val($remaining_value);
        }
        if (count($flags_off) < count($flags_on)) {
            // Most flags are on, it will be more readable and future-proof to start from E_ALL and negate
            return new Bitwise_And($builder_factory->const_fetch('E_ALL'), new Bitwise_Not(self::build_bitwise_flag_expression(...$flags_off)));
        }
        // Most flags are off, so just specify the ones that are on
        return self::build_bitwise_flag_expression(...$flags_on);
    }
    private static function list_error_level_constants(): iterable
    {
        foreach (get_defined_constants(true)['Core'] as $const_name => $const_value) {
            if (str_starts_with($const_name, 'E_') && $const_name !== 'E_ALL' && $const_name !== 'E_STRICT') {
                yield $const_name => $const_value;
            }
        }
    }
    private static function build_bitwise_flag_expression(Expr ...$expressions): Expr
    {
        $expr = array_shift($expressions);
        while ($expressions !== []) {
            $expr = new Bitwise_Or($expr, array_shift($expressions));
        }
        return $expr;
    }
}