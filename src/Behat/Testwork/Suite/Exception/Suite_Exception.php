<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite\Exception;

use Behat\Testwork\Exception\Testwork_Exception;
use Exception;
use InvalidArgumentException;
/**
 * Represents a suite exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Suite_Exception extends InvalidArgumentException implements Testwork_Exception
{
    /**
     * Initializes exception.
     *
     * @param string         $message
     * @param string         $name
     */
    public function __construct($message, private $name, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
    /**
     * Returns name of the suite that caused exception.
     *
     * @return string
     */
    public function get_suite_name()
    {
        return $this->name;
    }
}