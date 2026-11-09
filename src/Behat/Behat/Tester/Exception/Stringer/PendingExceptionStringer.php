<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Exception\Stringer;

use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Testwork\Exception\Stringer\Exception_Stringer;
use Exception;
/**
 * Strings pending exceptions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Pending_Exception_Stringer implements Exception_Stringer
{
    public function supports_exception(Exception $exception): bool
    {
        return $exception instanceof Pending_Exception;
    }
    public function string_exception(Exception $exception, $verbosity): string
    {
        return trim($exception->get_message());
    }
}