<?php

declare (strict_types=1);
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
 * Handles method not found exceptions.
 *
 * @see ExceptionHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Method_Not_Found_Handler implements Exception_Handler
{
    public const PATTERN = '/^Call to undefined method ([^:]+)::([^\)]+)\(\)$/';
    final public function supports_exception($exception)
    {
        if (!$exception instanceof Error) {
            return false;
        }
        return null !== $this->extract_non_existent_callable($exception);
    }
    final public function handle_exception($exception)
    {
        assert($exception instanceof Error);
        $this->handle_non_existent_method($this->extract_non_existent_callable($exception));
        return $exception;
    }
    /**
     * Override to handle non-existent method.
     */
    abstract public function handle_non_existent_method(array $callable);
    /**
     * Extract callable from exception.
     */
    private function extract_non_existent_callable(Error $exception): ?array
    {
        if (1 === preg_match(self::PATTERN, $exception->get_message(), $matches)) {
            return [$matches[1], $matches[2]];
        }
        return null;
    }
}