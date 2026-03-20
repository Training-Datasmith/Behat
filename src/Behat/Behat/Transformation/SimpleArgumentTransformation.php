<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Testwork\Call\Call_Center;
use ReflectionMethod;
/**
 * Represents a simple self-contained transformation capable of changing a single argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Simple_Argument_Transformation extends Transformation
{
    /**
     * Checks if transformation supports given pattern.
     *
     * @param string           $pattern
     *
     * @return bool
     */
    public static function supports_pattern_and_method($pattern, ReflectionMethod $method);
    /**
     * Returns transformation priority.
     *
     * @return int
     */
    public function get_priority();
    /**
     * Checks if transformation supports argument.
     *
     * @param int|string $argumentIndex
     *
     * @return bool
     */
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_argument_value);
    /**
     * Transforms argument value using transformation and returns a new one.
     *
     * @param int|string $argumentIndex
     */
    public function transform_argument(Call_Center $call_center, Definition_Call $definition_call, $argument_index, $argument_value);
}