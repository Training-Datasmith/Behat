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

/**
 * Step definition pattern.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Pattern
{
    public function __construct(private string $suggested_method_name, private string $pattern, private int $placeholder_count = 0)
    {
    }
    /**
     * Returns canonical step text.
     *
     * @deprecated see getSuggestedMethodName
     */
    public function get_canonical_text(): string
    {
        return $this->suggested_method_name;
    }
    public function get_suggested_method_name(): string
    {
        return $this->suggested_method_name;
    }
    public function get_pattern(): string
    {
        return $this->pattern;
    }
    public function get_placeholder_count(): int
    {
        return $this->placeholder_count;
    }
}