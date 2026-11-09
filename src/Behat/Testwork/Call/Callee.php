<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

/**
 * Represents callable object.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Callee
{
    /**
     * Returns callee definition path.
     *
     * @return string
     */
    public function get_path();

    /**
     * Returns callee description.
     *
     * @return string
     */
    public function get_description();

    /**
     * Returns true if callee is a method, false otherwise.
     *
     * @return bool
     */
    public function is_a_method();

    /**
     * Returns true if callee is an instance (non-static) method, false otherwise.
     *
     * @return bool
     */
    public function is_an_instance_method();

    /**
     * Returns callable.
     *
     * @return callable|array{class-string, string}
     */
    public function get_callable();

    /**
     * Returns callable reflection.
     *
     * @return ReflectionFunctionAbstract
     */
    public function get_reflection();
}
