<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Result\Test_Result;

final readonly class JSON_Step_Printer implements Step_Printer
{
    public function __construct(private Exception_Presenter $exception_presenter)
    {
    }

    public function print_step(Formatter $formatter, Scenario $scenario, StepNode $step, Step_Result $result): void
    {
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $message = $step->getKeyword() . ' ' . $step->getText();
        if ($result instanceof Exception_Result && $result->has_exception()) {
            $message .= ': ' . $this->exception_presenter->present_exception($result->get_exception(), apply_editor_url: false);
        }
        $attributes = ['message' => $message];
        $output_printer->add_current_scenario_attributes(['line' => $step->getLine()]);
        switch ($result->get_result_code()) {
            case Test_Result::FAILED:
                $attributes['type'] = 'failed';
                $output_printer->add_scenario_child('failures', $attributes);
                break;
            case Test_Result::PENDING:
                $attributes['type'] = 'pending';
                $output_printer->add_scenario_child('failures', $attributes);
                break;
            case Test_Result::UNDEFINED:
                $attributes['type'] = 'undefined';
                $output_printer->add_scenario_child('failures', $attributes);
                break;
        }
    }
}
