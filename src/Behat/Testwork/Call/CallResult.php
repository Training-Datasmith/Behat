<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Call;

use Exception;
/**
 * Represents result of the call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Call_Result
{
    /**
     * Initializes call result.
     *
     * @param string|null $stdOut
     */
    public function __construct(private readonly Call $call, private $return, private readonly ?Exception $exception = null, private $std_out = null)
    {
    }
    /**
     * Returns call.
     */
    public function get_call(): Call
    {
        return $this->call;
    }
    /**
     * Returns call return value.
     */
    public function get_return()
    {
        return $this->return;
    }
    /**
     * Check if call thrown exception.
     *
     * @phpstan-assert-if-true Exception $this->exception
     * @phpstan-assert-if-true Exception $this->getException()
     */
    public function has_exception(): bool
    {
        return $this->exception instanceof Exception;
    }
    /**
     * Returns exception thrown by call (if any).
     */
    public function get_exception(): ?Exception
    {
        return $this->exception;
    }
    /**
     * Checks if call produced stdOut.
     */
    public function has_std_out(): bool
    {
        return null !== $this->std_out;
    }
    /**
     * Returns stdOut produced by call (if any).
     *
     * @return string|null
     */
    public function get_std_out()
    {
        return $this->std_out;
    }
}