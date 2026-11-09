<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

/**
 * Represents a way to retrieve suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Suite_Repository
{
    /**
     * Returns all available suites.
     *
     * @return Suite[]
     */
    public function get_suites();
}
