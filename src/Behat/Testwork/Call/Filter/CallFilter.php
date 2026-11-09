<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Call\Filter;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Call_Center;
/**
 * Filters call before its being made and returns a new call.
 *
 * @see CallCenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Call_Filter
{
    /**
     * Checks if filter supports a call.
     *
     * @return bool
     */
    public function supports_call(Call $call);
    /**
     * Filters a call and returns a new one.
     *
     * @return Call
     */
    public function filter_call(Call $call);
}