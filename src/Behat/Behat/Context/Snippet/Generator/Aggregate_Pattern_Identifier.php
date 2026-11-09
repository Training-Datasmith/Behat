<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

/**
 * Uses multiple child identifiers - the first one that returns non-null result would
 * be the winner.
 */
final readonly class Aggregate_Pattern_Identifier implements Pattern_Identifier
{
    /**
     * Initialises identifier.
     *
     * @param Pattern_Identifier[] $identifiers
     */
    public function __construct(private array $identifiers)
    {
    }

    public function guess_pattern_type($context_class)
    {
        foreach ($this->identifiers as $identifier) {
            $pattern = $identifier->guess_pattern_type($context_class);
            if (null !== $pattern) {
                return $pattern;
            }
        }

        return null;
    }
}
