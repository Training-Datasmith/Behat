<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Exception;

use InvalidArgumentException;

/**
 * Represents an exception thrown because user provided bad output path.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Bad_Output_Path_Exception extends InvalidArgumentException implements Printer_Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $path
     */
    public function __construct($message, private $path)
    {
        parent::__construct($message);
    }

    /**
     * Returns exception causing path.
     *
     * @return string
     */
    public function get_path()
    {
        return $this->path;
    }
}
