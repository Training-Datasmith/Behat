<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\Outline_Printer;
use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Tester\Result\Undefined_Step_Result;
use Behat\Gherkin\Node\Example_Table_Node;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Outline_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints outline header with outline steps and table header.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Outline_Printer implements Outline_Printer
{
    /**
     * @var string
     */
    private $indent_text;
    private readonly string $sub_indent_text;
    /**
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(private readonly Scenario_Printer $scenario_printer, private readonly Step_Printer $step_printer, private readonly Result_To_String_Converter $result_converter, $indentation = 4, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }
    public function print_header(Formatter $formatter, Feature_Node $feature, Outline_Node $outline): void
    {
        $this->scenario_printer->print_header($formatter, $feature, $outline);
        $this->print_examples_steps($formatter, $outline, $outline->get_steps());
        $this->print_examples_table_header($formatter->get_output_printer(), $outline->get_example_table());
    }
    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $formatter->get_output_printer()->writeln();
    }
    /**
     * Prints outline steps.
     *
     * @param StepNode[]  $steps
     */
    private function print_examples_steps(Formatter $formatter, Outline_Node $outline, array $steps): void
    {
        foreach ($steps as $step) {
            $this->step_printer->print_step($formatter, $outline, $step, new Undefined_Step_Result());
        }
        $formatter->get_output_printer()->writeln();
    }
    /**
     * Prints examples table header.
     */
    private function print_examples_table_header(Output_Printer $printer, Example_Table_Node $table): void
    {
        $printer->writeln(sprintf('%s{+keyword}%s:{-keyword}', $this->indent_text, $table->get_keyword()));
        $row_num = 0;
        $wrapper = $this->get_wrapper_closure();
        $row = $table->get_row_as_string_with_wrapped_values($row_num, $wrapper);
        $printer->writeln(sprintf('%s%s', $this->sub_indent_text, $row));
    }
    /**
     * Creates wrapper-closure for the example header.
     *
     * @return callable
     */
    private function get_wrapper_closure()
    {
        $style = $this->result_converter->convert_result_code_to_string(Test_Result::SKIPPED);
        return fn($col): string => sprintf('{+%s_param}%s{-%s_param}', $style, $col, $style);
    }
}