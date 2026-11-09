<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Behat\Testwork\Call\Exception\Call_Error_Exception;
use Behat\Testwork\Exception\Testwork_Exception;
use Exception;

/**
 * Strings Testwork exceptions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Testwork_Exception_Stringer implements Exception_Stringer
{
    public function supports_exception(Exception $exception): bool
    {
        return $exception instanceof Testwork_Exception || $exception instanceof Call_Error_Exception;
    }

    public function string_exception(Exception $exception, $verbosity): string
    {
        return trim($exception->getMessage());
    }
}
