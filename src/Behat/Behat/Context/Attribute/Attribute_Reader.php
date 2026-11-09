<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Attribute;

use ReflectionMethod;

/**
 * Reads Attributes of a provided context method into a Callee.
 *
 * @see Attribute_Context_Reader
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Attribute_Reader
{
    /**
     * Reads all callees associated with a provided method.
     *
     * @return list<Runtime_Callee>
     */
    public function read_callees(string $context_class, ReflectionMethod $method);
}
