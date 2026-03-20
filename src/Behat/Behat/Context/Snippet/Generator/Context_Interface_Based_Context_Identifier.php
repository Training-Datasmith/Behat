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
use Behat\Behat\Context\Snippet_Accepting_Context;
/**
 * Identifier that uses context interfaces to guess which one is target.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of --snippets-for and will be removed in 4.0
 */
final class Context_Interface_Based_Context_Identifier implements Target_Context_Identifier
{
    public function guess_target_context_class(Context_Environment $environment)
    {
        foreach ($environment->get_context_classes() as $class) {
            if (in_array(Snippet_Accepting_Context::class, class_implements($class))) {
                return $class;
            }
        }
        return null;
    }
}