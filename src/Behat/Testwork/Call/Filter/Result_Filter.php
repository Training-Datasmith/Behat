<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Filter;

use Behat\Testwork\Call\Call_Result;

/**
 * Filters call results and produces new ones.
 *
 * @see Call_Center
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Result_Filter
{
    /**
     * Checks if filter supports call result.
     *
     * @return bool
     */
    public function supports_result(Call_Result $result);

    /**
     * Filters call result and returns a new result.
     *
     * @return Call_Result
     */
    public function filter_result(Call_Result $result);
}
