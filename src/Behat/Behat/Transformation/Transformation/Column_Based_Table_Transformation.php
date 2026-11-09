<?php

declare(strict_types=1);
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
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Runtime_Callee;
use ReflectionMethod;
use Stringable;

/**
 * Column-based table transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Column_Based_Table_Transformation extends Runtime_Callee implements Stringable, Simple_Argument_Transformation
{
    public const PATTERN_REGEX = '/^table\:(?:\*|[[:print:]]+)$/u';

    public static function supports_pattern_and_method($pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct(private $pattern, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }

    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_argument_value)
    {
        if (!$argument_argument_value instanceof TableNode) {
            return false;
        }

        return $this->pattern === 'table:' . implode(',', $argument_argument_value->getRow(0)) || $this->pattern === 'table:*';
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
        return 50;
    }

    public function get_pattern()
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return 'ColumnTableTransform ' . $this->pattern;
    }
}
