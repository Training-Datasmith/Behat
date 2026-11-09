<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification\Locator;

use Behat\Testwork\Suite\Suite;

/**
 * Provides a custom way to locate specification by provided suite and locator string.
 *
 * @see Specification_Finder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template-covariant T
 */
interface Specification_Locator
{
    /**
     * Returns array of strings representing examples of supported specification locators.
     *
     * @return string[]
     */
    public function get_locator_examples();

    /**
     * Locates specifications and wraps them into iterator.
     *
     * @param string|null $locator
     *
     * @return Specification_Iterator<T>
     */
    public function locate_specifications(Suite $suite, $locator);
}
