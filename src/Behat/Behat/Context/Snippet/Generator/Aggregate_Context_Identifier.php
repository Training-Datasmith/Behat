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

use Behat\Behat\Context\Environment\Context_Environment;

/**
 * Uses multiple child identifiers - the first one that returns non-null result would
 * be the winner.
 *
 * This behaviour was introduced in 3.x to support the BC for interface-focused
 * context identifier, while providing better user experience (no need to explicitly
 * call `--snippets-for` on `--append-snippets` when contexts do not implement any
 * snippet accepting interfaces).
 */
final readonly class Aggregate_Context_Identifier implements Target_Context_Identifier
{
    /**
     * Initialises identifier.
     *
     * @param Target_Context_Identifier[] $identifiers
     */
    public function __construct(private array $identifiers)
    {
    }

    public function guess_target_context_class(Context_Environment $environment)
    {
        foreach ($this->identifiers as $identifier) {
            $context_class = $identifier->guess_target_context_class($environment);
            if (null !== $context_class) {
                return $context_class;
            }
        }

        return null;
    }
}
