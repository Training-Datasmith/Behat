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
 * Represents an exception thrown because user did not provide an output path.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Missing_Output_Path_Exception extends InvalidArgumentException implements Printer_Exception
{
}
