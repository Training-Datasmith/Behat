<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Exception;

use Behat\Testwork\Exception\Testwork_Exception;
use RuntimeException;

/**
 * Represents exception throw during attempt to prioritise execution with a non-existent algorithm.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class Invalid_Order_Exception extends RuntimeException implements Testwork_Exception
{
}
