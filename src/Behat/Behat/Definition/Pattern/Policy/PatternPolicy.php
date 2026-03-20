<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
/**
 * Defines a way to handle custom definition patterns.
 *
 * @see PatternTransformer
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Pattern_Policy
{
    /**
     * Checks if policy supports pattern type.
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function supports_pattern_type($type);
    /**
     * Generates pattern for step text.
     *
     * @param string $stepText
     *
     * @return Pattern
     */
    public function generate_pattern($step_text);
    /**
     * Checks if policy supports pattern.
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function supports_pattern($pattern);
    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     *
     * @return string
     */
    public function transform_pattern_to_regex($pattern);
}