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
use Behat\Gherkin\Node\Table_Node;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Runtime_Callee;
use ReflectionMethod;
use Stringable;
final class Table_Column_Transformation extends Runtime_Callee implements Stringable, Simple_Argument_Transformation
{
    public const PATTERN_REGEX = '/^column\:[[:print:]]+$/u';
    public static function supports_pattern_and_method($pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }
    public function __construct(private readonly string $pattern, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_argument_value): bool
    {
        // The argument passed initially will be a TableNode but if a column transformation
        // has already been applied then this will have been transformed into an array already,
        // so we need to accept both possibilities
        if (!$argument_argument_value instanceof Table_Node && !is_array($argument_argument_value)) {
            return false;
        }
        if (!str_starts_with($this->pattern, 'column:')) {
            return false;
        }
        $column_names = explode(',', substr($this->pattern, 7));
        if ($argument_argument_value instanceof Table_Node) {
            $table_headings = $argument_argument_value->get_row(0);
            return array_intersect($column_names, $table_headings) !== [];
        }
        foreach ($argument_argument_value as $row) {
            $row_has_column = false;
            foreach ($column_names as $column_name) {
                if (isset($row[$column_name])) {
                    $row_has_column = true;
                }
            }
            if (!$row_has_column) {
                return false;
            }
        }
        return true;
    }
    /**
     * @param TableNode|array $argumentValue
     */
    public function transform_argument(Call_Center $call_center, Definition_Call $definition_call, $argument_index, $argument_value): array
    {
        $column_names = explode(',', substr($this->pattern, 7));
        $rows = [];
        foreach ($argument_value as $row) {
            foreach ($column_names as $column_name) {
                if (isset($row[$column_name])) {
                    $call = new Transformation_Call($definition_call->get_environment(), $definition_call->get_callee(), $this, [$row[$column_name]]);
                    $result = $call_center->make_call($call);
                    if ($result->has_exception()) {
                        throw $result->get_exception();
                    }
                    $row[$column_name] = $result->get_return();
                }
            }
            $rows[] = $row;
        }
        return $rows;
    }
    /**
     * The priority of this transformer needs to be less that the priority of the other table transformers because
     * we want to be able to transform whole tables or whole rows before we attempt to transform any column.
     */
    public function get_priority(): int
    {
        return 30;
    }
    public function get_pattern(): string
    {
        return $this->pattern;
    }
    public function __toString(): string
    {
        return 'TableColumnTransform ' . $this->pattern;
    }
}