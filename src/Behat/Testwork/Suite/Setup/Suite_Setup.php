<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite\Setup;

use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\Suite_Bootstrapper;
/**
 * Sets up supported test suite.
 *
 * @see SuiteBootstrapper
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Suite_Setup
{
    /**
     * Checks if setup supports provided suite.
     *
     * @return bool
     */
    public function supports_suite(Suite $suite);
    /**
     * Sets up provided suite.
     */
    public function setup_suite(Suite $suite);
}