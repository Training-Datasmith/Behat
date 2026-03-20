<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Prepares and tests provided step object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Step_Tester
{
    /**
     * Sets up step for a test.
     *
     * @param bool     $skip
     *
     * @return Setup
     */
    public function set_up(Environment $env, Feature_Node $feature, Step_Node $step, $skip);
    /**
     * Tests step.
     *
     * @param bool     $skip
     *
     * @return StepResult
     */
    public function test(Environment $env, Feature_Node $feature, Step_Node $step, $skip);
    /**
     * Tears down step after a test.
     *
     * @param bool     $skip
     *
     * @return Teardown
     */
    public function tear_down(Environment $env, Feature_Node $feature, Step_Node $step, $skip, Step_Result $result);
}