<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Event;

use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Represents an event after step has been tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Step_Tested extends Step_Tested implements After_Tested
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly Feature_Node $feature, private readonly Step_Node $step, private readonly Step_Result $result, private readonly Teardown $teardown)
    {
        parent::__construct($env);
    }
    /**
     * Returns feature.
     */
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
    /**
     * Returns step node.
     */
    public function get_step(): Step_Node
    {
        return $this->step;
    }
    /**
     * Returns current test result.
     */
    public function get_test_result(): Step_Result
    {
        return $this->result;
    }
    /**
     * Returns current test teardown.
     */
    public function get_teardown(): Teardown
    {
        return $this->teardown;
    }
    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     */
    public function has_output(): bool
    {
        if ($this->teardown_has_output()) {
            return true;
        }
        if ($this->result_has_exception()) {
            return true;
        }
        return $this->result_call_has_output();
    }
    /**
     * Checks if step teardown has output.
     *
     * @return bool
     */
    private function teardown_has_output()
    {
        return $this->teardown->has_output();
    }
    /**
     * Checks if result has produced exception.
     */
    private function result_has_exception(): bool
    {
        return $this->result instanceof Exception_Result && $this->result->get_exception();
    }
    /**
     * Checks if result is executed and call result has produced exception or stdOut.
     *
     * @return bool
     */
    private function result_call_has_output()
    {
        if (!$this->result instanceof Executed_Step_Result) {
            return false;
        }
        if ($this->result->get_call_result()->has_std_out()) {
            return true;
        }
        return $this->result->get_call_result()->has_exception();
    }
}