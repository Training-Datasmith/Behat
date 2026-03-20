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

/**
 * Represents any call made inside testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Call
{
    /**
     * Returns callee.
     *
     * @return Callee
     */
    public function get_callee();
    /**
     * Returns bound callable.
     *
     * @return callable
     */
    public function get_bound_callable();
    /**
     * Returns call arguments.
     *
     * @return array
     */
    public function get_arguments();
    /**
     * Returns call error reporting level.
     *
     * @return int|null
     */
    public function get_error_reporting_level();
}