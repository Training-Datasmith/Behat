<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Context_Class;

/**
 * Resolves arbitrary context strings into a context classes.
 *
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Class_Resolver
{
    /**
     * Checks if resolvers supports provided class.
     *
     * @return bool
     */
    public function supports_class($context_string);

    /**
     * Resolves context class.
     *
     * @return string
     */
    public function resolve_class($context_class);
}
