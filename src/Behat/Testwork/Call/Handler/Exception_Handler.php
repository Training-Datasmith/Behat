<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Throwable;

/**
 * Handles exceptions.
 *
 * @see Call_Center
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Exception_Handler
{
    /**
     * Checks if handler supports exception.
     *
     * @param Throwable $exception
     *
     * @return bool
     */
    public function supports_exception($exception);

    /**
     * Handles exception and returns new one if necessary.
     *
     * @param Throwable $exception
     *
     * @return Throwable
     */
    public function handle_exception($exception);
}
