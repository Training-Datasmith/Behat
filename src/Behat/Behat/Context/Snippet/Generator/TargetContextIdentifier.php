<?php

declare (strict_types=1);
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
 * Identifies target context for snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Target_Context_Identifier
{
    /**
     * Attempts to guess the target context class from the environment.
     *
     * @return string|null
     */
    public function guess_target_context_class(Context_Environment $environment);
}