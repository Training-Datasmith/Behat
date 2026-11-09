<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Helper_Container\Exception;

use Behat\Testwork\Environment\Exception\Environment_Exception;

/**
 * All HelperContainer exceptions implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Helper_Container_Exception extends Container_Exception_Interface, Environment_Exception
{
}
