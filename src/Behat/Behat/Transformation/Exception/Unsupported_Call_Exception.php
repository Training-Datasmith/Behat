<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Exception;

use Behat\Testwork\Call\Call;
use InvalidArgumentException;

/**
 * Represents an exception caused by an attempt to filter an unsupported call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Unsupported_Call_Exception extends InvalidArgumentException implements Transformation_Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     */
    public function __construct($message, private readonly Call $call)
    {
        parent::__construct($message);
    }

    /**
     * Returns a call that caused exception.
     */
    public function get_call(): Call
    {
        return $this->call;
    }
}
