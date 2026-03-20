<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context;

use Behat\Behat\Context\Snippet\Generator\Context_Snippet_Generator;
/**
 * Context that implements this interface is treated as a custom-snippet-friendly context.
 *
 * @see ContextSnippetGenerator
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated will be removed in 4.0. Use --snippets-for and --snippets-type CLI options instead
 */
interface Custom_Snippet_Accepting_Context extends Snippet_Accepting_Context
{
    /**
     * Returns type of the snippets that this context accepts.
     *
     * Behat implements a couple of types by default: "regex" and "turnip"
     *
     * @return string
     */
    public static function get_accepted_snippet_type();
}