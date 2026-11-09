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
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;

/**
 * Tests provided collection of steps against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Step_Container_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Tester $step_tester)
    {
    }

    /**
     * Tests container.
     *
     * @param bool                $skip
     *
     * @return list<Test_Result>
     */
    public function test(Environment $env, FeatureNode $feature, StepContainerInterface $container, $skip): array
    {
        $results = [];
        foreach ($container->getSteps() as $step) {
            $setup = $this->step_tester->set_up($env, $feature, $step, $skip);
            $skip_setup = !$setup->is_successful() || $skip;
            $test_result = $this->step_tester->test($env, $feature, $step, $skip_setup);
            $skip = !$test_result->is_passed() || $skip;
            $teardown = $this->step_tester->tear_down($env, $feature, $step, $skip_setup, $test_result);
            $skip = $skip || $skip_setup || !$teardown->is_successful();
            $integer_result = new Integer_Test_Result($test_result->get_result_code());
            $results[] = new Test_With_Setup_Result($setup, $integer_result, $teardown);
        }

        return $results;
    }
}
