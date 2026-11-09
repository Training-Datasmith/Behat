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
 * Identifier that always returns same context, if it is defined in the suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Fixed_Context_Identifier implements Target_Context_Identifier
{
    public function __construct(private ?string $context_class = null)
    {
    }

    public function guess_target_context_class(Context_Environment $environment): ?string
    {
        if (is_string($this->context_class) && $environment->has_context_class($this->context_class)) {
            return $this->context_class;
        }

        return null;
    }
}
