<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Context;
use Behat\Testwork\Environment\Environment;

/**
 * Represents test environment based on a collection of contexts.
 *
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Context_Environment extends Environment
{
    /**
     * Checks if environment has any contexts registered.
     *
     * @return bool
     */
    public function has_contexts();

    /**
     * Returns list of registered context classes.
     *
     * @return list<class-string<Context>>
     */
    public function get_context_classes();

    /**
     * Checks if environment contains context with the specified class name.
     *
     * @param class-string<Context> $class
     *
     * @return bool
     */
    public function has_context_class($class);
}
