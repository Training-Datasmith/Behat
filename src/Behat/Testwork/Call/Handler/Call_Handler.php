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

use Behat\Testwork\Call\Call;

/**
 * Handles calls and produces call results.
 *
 * @see Call_Center
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Call_Handler
{
    /**
     * Checks if handler supports call.
     *
     * @return bool
     */
    public function supports_call(Call $call);

    /**
     * Handles call and returns call result.
     *
     * @return Call_Result
     */
    public function handle_call(Call $call);
}
