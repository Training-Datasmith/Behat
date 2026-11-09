<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests background from a provided feature object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Background_Tester
{
    /**
     * Sets up background for a test.
     *
     * @param bool     $skip
     *
     * @return Setup
     */
    public function set_up(Environment $env, FeatureNode $feature, $skip);

    /**
     * Tests background.
     *
     * @param bool     $skip
     *
     * @return Test_Result
     */
    public function test(Environment $env, FeatureNode $feature, $skip);

    /**
     * Tears down background after a test.
     *
     * @param bool     $skip
     *
     * @return Teardown
     */
    public function tear_down(Environment $env, FeatureNode $feature, $skip, Test_Result $result);
}
