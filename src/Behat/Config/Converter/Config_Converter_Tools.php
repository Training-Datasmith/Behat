<?php

declare(strict_types=1);

namespace Behat\Config\Converter;

use InvalidArgumentException;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Use_;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;

class Config_Converter_Tools
{
    private static ?BuilderFactory $builder_factory = null;

    public static function add_method_call(string $class, string $method_name, array $arguments, Expr $expr): Expr
    {
        $builder_factory = self::get_builder_factory();
        $arguments = self::remove_default_arguments((new ReflectionClass($class))->getMethod($method_name), $arguments);
        $args = $builder_factory->args(self::replace_class_references($arguments));

        return $builder_factory->methodCall($expr, $method_name, $args);
    }

    private static function remove_default_arguments(ReflectionMethod $method, array $arguments): array
    {
        $has_removed_any = false;
        $result = [];
        foreach ($method->getParameters() as $index => $parameter) {
            if ($parameter->isVariadic()) {
                // We can't do anything useful (easily) for methods with variadics, just return the originals
                return $arguments;
            }
            // Config objects can export their method arguments as positional or named arguments.
            $arg_key = array_key_exists($parameter->getName(), $arguments) ? $parameter->getName() : $index;
            // The Config object must provide a value for every argument (defaults are stripped here). If we are
            // missing arguments, that implies the ->toPhpExpr implementation is out of sync with the method definition.
            if (!array_key_exists($arg_key, $arguments)) {
                throw new InvalidArgumentException(sprintf('Missing argument #%d (%s) for %s::%s', $index, $parameter->getName(), $method->getDeclaringClass()->getName(), $method->getName()));
            }
            $arg_value = $arguments[$arg_key];
            if ($parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() === $arg_value) {
                // We can skip this, it has the default value. That means we'll need to specify any following arguments
                // by name even if we were given them as positional.
                $has_removed_any = true;
            } else {
                $result[$has_removed_any ? $parameter->getName() : $arg_key] = $arg_value;
            }
            // Record that this argument has been used
            unset($arguments[$arg_key]);
        }
        // We should have applied all arguments that were provided. If not, that implies the ->toPhpExpr implementation
        // is out of sync with the method definition.
        if ($arguments !== []) {
            throw new InvalidArgumentException(sprintf('Too many arguments provided for %s::%s (%s)', $method->getDeclaringClass()->getName(), $method->getName(), implode(', ', array_keys($arguments))));
        }

        return $result;
    }

    public static function create_object(string $class_name): New_
    {
        $builder_factory = self::get_builder_factory();

        return $builder_factory->new(new FullyQualified($class_name));
    }

    public static function add_arguments_to_constructor(array $arguments, New_ $expr): void
    {
        $builder_factory = self::get_builder_factory();
        $expr->args = $builder_factory->args(self::replace_class_references($arguments));
    }

    public static function create_use_statement(string $class_name): Use_
    {
        $builder_factory = self::get_builder_factory();

        return $builder_factory->use($class_name)->getNode();
    }

    private static function get_builder_factory(): BuilderFactory
    {
        if (!self::$builder_factory instanceof BuilderFactory) {
            self::$builder_factory = new BuilderFactory();
        }

        return self::$builder_factory;
    }

    private static function replace_class_references(array $arguments): array
    {
        return array_map(static fn ($arg) => is_string($arg) && class_exists($arg) ? self::get_class_const_reference($arg, 'class') : $arg, $arguments);
    }

    private static function get_class_const_reference(string $class_name, string $constant_name): Expr
    {
        $builder_factory = self::get_builder_factory();

        return $builder_factory->classConstFetch($class_name, $constant_name);
    }

    public static function find_reference_to_class_constant(string $class_name, mixed $setting_value): mixed
    {
        $consts = (new ReflectionClass($class_name))->getConstants(ReflectionClassConstant::IS_PUBLIC);
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
            return $builder_factory->constFetch('E_ALL');
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
                $flags_on[] = $builder_factory->constFetch($const_name);
            } else {
                $flags_off[] = $builder_factory->constFetch($const_name);
            }
        }
        if ($remaining_value !== 0) {
            // This is an edge case, their value includes a number that is not defined as a PHP error_reporting level
            // Keep it as a constant value so that the configuration doesn't change and the user can review it
            $flags_on[] = $builder_factory->val($remaining_value);
        }
        if (count($flags_off) < count($flags_on)) {
            // Most flags are on, it will be more readable and future-proof to start from E_ALL and negate
            return new BitwiseAnd($builder_factory->constFetch('E_ALL'), new BitwiseNot(self::build_bitwise_flag_expression(...$flags_off)));
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
            $expr = new BitwiseOr($expr, array_shift($expressions));
        }

        return $expr;
    }
}
