<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Service_Container\Exception;

use InvalidArgumentException;
/**
 * Represents exception thrown during configuration load.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Configuration_Loading_Exception extends InvalidArgumentException implements Service_Container_Exception
{
}