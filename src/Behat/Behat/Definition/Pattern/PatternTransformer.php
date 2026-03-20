<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Pattern;

use Behat\Behat\Definition\Exception\Unknown_Pattern_Exception;
use Behat\Behat\Definition\Exception\Unsupported_Pattern_Type_Exception;
use Behat\Behat\Definition\Pattern\Policy\Pattern_Policy;
/**
 * Transforms patterns using registered policies.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pattern_Transformer
{
    /**
     * @var PatternPolicy[]
     */
    private array $policies = [];
    /**
     * @var string[]
     */
    private array $pattern_to_regexp_cache = [];
    /**
     * Registers pattern policy.
     */
    public function register_pattern_policy(Pattern_Policy $policy): void
    {
        $this->policies[] = $policy;
        $this->pattern_to_regexp_cache = [];
    }
    /**
     * Generates pattern.
     *
     * @param string $type
     * @param string $stepText
     *
     * @return Pattern
     *
     * @throws UnsupportedPatternTypeException
     */
    public function generate_pattern($type, $step_text)
    {
        foreach ($this->policies as $policy) {
            if ($policy->supports_pattern_type($type)) {
                return $policy->generate_pattern($step_text);
            }
        }
        throw new Unsupported_Pattern_Type_Exception(sprintf('Can not find policy for a pattern type `%s`.', $type), $type);
    }
    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     *
     * @return string
     *
     * @throws UnknownPatternException
     */
    public function transform_pattern_to_regex($pattern)
    {
        if (!isset($this->pattern_to_regexp_cache[$pattern])) {
            $this->pattern_to_regexp_cache[$pattern] = $this->transform_pattern_to_regex_with_supported_policy($pattern);
        }
        return $this->pattern_to_regexp_cache[$pattern];
    }
    /**
     * Tests if a pattern matches a given step text.
     *
     * If the pattern does not match, returns false. Otherwise, returns an array of the portions of the
     * step text that match any placeholders within the pattern.
     *
     * @return false|array<int|string,string>
     */
    public function match_pattern(string $pattern, string $step_text): false|array
    {
        $regex = $this->transform_pattern_to_regex($pattern);
        if (!preg_match($regex, $step_text, $matches)) {
            return false;
        }
        return $matches;
    }
    /**
     * @param string $pattern
     *
     * @return string
     *
     * @throws UnknownPatternException
     */
    private function transform_pattern_to_regex_with_supported_policy($pattern)
    {
        foreach ($this->policies as $policy) {
            if ($policy->supports_pattern($pattern)) {
                return $policy->transform_pattern_to_regex($pattern);
            }
        }
        throw new Unknown_Pattern_Exception(sprintf('Can not find policy for a pattern `%s`.', $pattern), $pattern);
    }
}