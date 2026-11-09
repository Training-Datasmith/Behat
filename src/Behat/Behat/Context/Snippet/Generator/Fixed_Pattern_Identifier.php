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
 * Identifier that always returns same pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Fixed_Pattern_Identifier implements Pattern_Identifier
{
    public function __construct(private ?string $pattern_type = null)
    {
    }

    public function guess_pattern_type($context_class): ?string
    {
        return $this->pattern_type;
    }
}
