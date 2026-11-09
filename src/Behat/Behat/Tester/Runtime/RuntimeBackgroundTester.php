<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\Background_Tester;
use Behat\Behat\Tester\Exception\Feature_Has_No_Background_Exception;
use Behat\Behat\Tester\Step_Container_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Tester executing background tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Runtime_Background_Tester implements Background_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Container_Tester $container_tester)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, $skip): Setup
    {
        return new Successful_Setup();
    }
    public function test(Environment $env, Feature_Node $feature, $skip): Test_Result
    {
        $background = $feature->get_background();
        if (null === $background) {
            throw new Feature_Has_No_Background_Exception(sprintf('Feature `%s` has no background that could be tested.', $feature->get_file()), $feature);
        }
        if (!$background->has_steps()) {
            return new Integer_Test_Result(Test_Result::PASSED);
        }
        $results = $this->container_tester->test($env, $feature, $background, $skip);
        return new Test_Results($results);
    }
    public function tear_down(Environment $env, Feature_Node $feature, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}