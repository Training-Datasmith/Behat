<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet;

/**
 * Provides snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Snippet_Repository
{
    /**
     * Returns all generated snippets.
     *
     * @return AggregateSnippet[]
     */
    public function get_snippets();
    /**
     * Returns steps for which there was no snippet generated.
     *
     * @return UndefinedStep[]
     */
    public function get_undefined_steps();
}