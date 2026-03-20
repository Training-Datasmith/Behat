<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\J_Unit;

use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints step with optional results.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author James Watson <james@sitepulse.org>
 */
class J_Unit_Step_Printer implements Step_Printer
{
    public function __construct(private readonly Exception_Presenter $exception_presenter)
    {
    }
    /**
     * Prints step using provided printer.
     */
    public function print_step(Formatter $formatter, Scenario $scenario, Step_Node $step, Step_Result $result): void
    {
        /** @var JUnitOutputPrinter $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $message = $step->get_keyword() . ' ' . $step->get_text();
        if ($result instanceof Exception_Result && $result->has_exception()) {
            $message .= ': ' . $this->exception_presenter->present_exception($result->get_exception(), applyEditorUrl: false);
        }
        $attributes = ['message' => $message];
        $output_printer->add_current_test_case_attributes(['line' => $step->get_line()]);
        switch ($result->get_result_code()) {
            case Test_Result::FAILED:
                $output_printer->add_testcase_child('failure', $attributes);
                break;
            case Test_Result::PENDING:
                $attributes['type'] = 'pending';
                $output_printer->add_testcase_child('error', $attributes);
                break;
            case Test_Result::UNDEFINED:
                $attributes['type'] = 'undefined';
                $output_printer->add_testcase_child('error', $attributes);
                break;
        }
    }
}