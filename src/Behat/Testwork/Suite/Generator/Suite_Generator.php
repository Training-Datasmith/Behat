<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Generator;

use Behat\Testwork\Suite\Suite;

/**
 * Generates a suite using provided name, settings and parameters.
 *
 * @see Suite_Registry
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Suite_Generator
{
    /**
     * Checks if generator support provided suite type and settings.
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function supports_type_and_settings($type, array $settings);

    /**
     * Generate suite with provided name and settings.
     *
     * @return Suite
     */
    public function generate_suite($suite_name, array $settings);
}
