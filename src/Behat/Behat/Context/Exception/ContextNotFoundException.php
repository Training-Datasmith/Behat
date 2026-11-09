<?php

declare (strict_types=1);
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
 * Represents an exception thrown when provided context class is not found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Not_Found_Exception extends InvalidArgumentException implements Context_Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $class
     */
    public function __construct($message, private $class)
    {
        parent::__construct($message);
    }
    /**
     * Returns not found classname.
     *
     * @return string
     */
    public function get_class()
    {
        return $this->class;
    }
}