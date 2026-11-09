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
 * Represents an exception caused by an unsupported pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Unsupported_Pattern_Type_Exception extends InvalidArgumentException implements Definition_Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $type
     */
    public function __construct($message, private $type)
    {
        parent::__construct($message);
    }

    /**
     * Returns pattern type that caused exception.
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }
}
