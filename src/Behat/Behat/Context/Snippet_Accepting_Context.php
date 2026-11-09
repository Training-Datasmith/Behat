<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context;

/**
 * Context that implements this interface is treated as a snippet-friendly context.
 *
 * @see Context_Snippet_Generator
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated will be removed in 4.0. Use --snippets-for CLI option instead
 */
interface Snippet_Accepting_Context extends Context
{
}
