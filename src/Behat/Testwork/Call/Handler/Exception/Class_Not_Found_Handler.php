<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler\Exception;

use Behat\Testwork\Call\Handler\Exception_Handler;
use Error;

/**
 * Handles class not found exceptions.
 *
 * @see Exception_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Class_Not_Found_Handler implements Exception_Handler
{
    public const PATTERN = "/^Class (?:'|\")([^'\"]+)(?:'|\") not found\$/";

    final public function supports_exception($exception)
    {
        if (!$exception instanceof Error) {
            return false;
        }

        return null !== $this->extract_non_existent_class($exception);
    }

    final public function handle_exception($exception)
    {
        assert($exception instanceof Error);
        $this->handle_non_existent_class($this->extract_non_existent_class($exception));

        return $exception;
    }

    /**
     * Override to handle non-existent class name.
     *
     * @param string $class
     */
    abstract public function handle_non_existent_class($class);

    /**
     * Extracts missing class name from the exception.
     */
    private function extract_non_existent_class(Error $exception): ?string
    {
        if (1 === preg_match(self::PATTERN, $exception->getMessage(), $matches)) {
            return $matches[1];
        }

        return null;
    }
}
