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

use Behat\Behat\Context\Custom_Snippet_Accepting_Context;

/**
 * Identifier that uses context interfaces to guess the pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of --snippet-type and will be removed in 4.0
 */
final class Context_Interface_Based_Pattern_Identifier implements Pattern_Identifier
{
    public function guess_pattern_type($context_class)
    {
        if (!in_array(Custom_Snippet_Accepting_Context::class, class_implements($context_class))) {
            return null;
        }

        return $context_class::get_accepted_snippet_type();
    }
}
