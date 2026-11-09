<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Exception;

use InvalidArgumentException;

/**
 * Represents an exception when provided class exists, but is not an acceptable as a context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Wrong_Context_Class_Exception extends InvalidArgumentException implements Context_Exception
{
    /**
     * Initializes exception.
     */
    public function __construct(string $message, private readonly string $class)
    {
        parent::__construct($message);
    }

    /**
     * Returns not found classname.
     */
    public function get_class(): string
    {
        return $this->class;
    }
}
