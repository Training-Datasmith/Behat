<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter\Exception;

use Behat\Testwork\Exception\Testwork_Exception;
use LogicException;

/**
 * Represents exception caused by timer handling.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Timer_Exception extends LogicException implements Testwork_Exception
{
}
