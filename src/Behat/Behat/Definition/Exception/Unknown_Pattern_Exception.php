<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use InvalidArgumentException;

/**
 * Represents an exception caused by an unrecognised definition pattern.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Unknown_Pattern_Exception extends InvalidArgumentException implements Definition_Exception
{
    public function __construct(string $message, private readonly string $pattern)
    {
        parent::__construct($message);
    }

    /**
     * Returns pattern that caused exception.
     */
    public function get_pattern(): string
    {
        return $this->pattern;
    }
}
