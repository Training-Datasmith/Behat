<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument\Exception;

use InvalidArgumentException;

/**
 * Represents an attempt to organise unsupported function arguments.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Unsupported_Function_Exception extends InvalidArgumentException implements Argument_Exception
{
}
