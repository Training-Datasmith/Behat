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
use Behat\Testwork\Event_Dispatcher\Event\Before_Teardown;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Represents an event before step teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Step_Teardown extends Step_Tested implements Before_Teardown
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly Feature_Node $feature, private readonly Step_Node $step, private readonly Step_Result $result)
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
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
    /**
     * Checks if step call produced any output (stdOut or exception).
     */
    public function has_output(): bool
    {
        if ($this->result_has_exception()) {
            return true;
        }
        return $this->result_call_has_output();
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