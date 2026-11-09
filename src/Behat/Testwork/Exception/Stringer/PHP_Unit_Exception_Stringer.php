<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Exception;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\ThrowableToStringMapper;
use PHPUnit_Framework_Exception;
use PHPUnit_Framework_TestFailure;
use Throwable;

/**
 * Strings PHPUnit assertion exceptions.
 *
 * @see Exception_Presenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PHP_Unit_Exception_Stringer implements Exception_Stringer
{
    public function supports_exception(Exception $exception): bool
    {
        return $exception instanceof PHPUnit_Framework_Exception || $exception instanceof \PHPUnit\Framework\Exception;
    }

    public function string_exception(Exception $exception, $verbosity): string
    {
        try {
            if (class_exists(ThrowableToStringMapper::class)) {
                return trim(ThrowableToStringMapper::map($exception));
            }
            if (class_exists(TestFailure::class)) {
                return trim(TestFailure::exceptionToString($exception));
            }
            if (class_exists(PHPUnit_Framework_TestFailure::class)) {
                return trim(PHPUnit_Framework_TestFailure::exceptionToString($exception));
            }

            return sprintf(<<<TEXT
            %s
            !! Could not render more details of this %s.
               Behat does not support automatically formatting assertion failures for your PHPUnit version.
               See %s for details.
            TEXT, $exception->getMessage(), $exception::class, self::class);
        } catch (Throwable $phpunit_exception) {
            return sprintf(<<<TEXT
            %s
            !! There was an error trying to render more details of this %s.
               You are probably using a PHPUnit version that Behat cannot automatically display failures for.
               See %s for details of PHPUnit support.
               [%s] %s at %s:%s
            TEXT, $exception->getMessage(), $exception::class, self::class, $phpunit_exception::class, $phpunit_exception->getMessage(), $phpunit_exception->getFile(), $phpunit_exception->getLine());
        }
    }
}
