<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Exception;

use Behat\Testwork\Output\Exception\Output_Exception;
use InvalidArgumentException;

/**
 * Represents an exception caused by a request for non-existent node visitor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Node_Visitor_Not_Found_Exception extends InvalidArgumentException implements Output_Exception
{
}
