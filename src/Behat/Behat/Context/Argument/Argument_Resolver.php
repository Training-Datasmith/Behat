<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Argument;

use ReflectionClass;

/**
 * Resolves arguments of context constructors.
 *
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Argument_Resolver
{
    /**
     * Resolves context constructor arguments.
     *
     * @param mixed[]         $arguments
     *
     * @return mixed[]
     */
    public function resolve_arguments(ReflectionClass $class_reflection, array $arguments);
}
