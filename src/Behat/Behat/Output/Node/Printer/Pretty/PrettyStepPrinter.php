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
use Behat\Behat\Output\Node\Printer\Helper\Step_Text_Painter;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Tester\Result\Defined_Step_Result;
use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Gherkin\Node\Argument_Interface;
use Behat\Gherkin\Node\Py_String_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
use Behat\Gherkin\Node\Table_Node;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints step.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Step_Printer implements Step_Printer
{
    /**
     * @var string
     */
    private $indent_text;
    private readonly string $sub_indent_text;
    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(private readonly Step_Text_Painter $text_painter, private readonly Result_To_String_Converter $result_converter, private readonly Pretty_Path_Printer $path_printer, private readonly Exception_Presenter $exception_presenter, $indentation = 4, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }
    public function print_step(Formatter $formatter, Scenario $scenario, Step_Node $step, Step_Result $result): void
    {
        if ($result->get_result_code() === Test_Result::SKIPPED) {
            $print_skipped = $formatter->get_parameter(Pretty_Formatter::PRINT_SKIPPED_STEPS_SETTING);
            if ($print_skipped === false) {
                return;
            }
        }
        $this->print_text($formatter->get_output_printer(), $step->get_keyword(), $step->get_text(), $result);
        $this->path_printer->print_step_path($formatter, $scenario, $step, $result, mb_strlen($this->indent_text, 'utf8'));
        $this->print_arguments($formatter, $step->get_arguments(), $result);
        $show_output = $formatter->get_parameter(Show_Output_Option::OPTION_NAME);
        if ($show_output === null || $show_output === Show_Output_Option::Yes || $show_output === Show_Output_Option::OnFail && !$result->is_passed()) {
            $this->print_std_out($formatter->get_output_printer(), $result);
        }
        $this->print_exception($formatter->get_output_printer(), $result);
    }
    /**
     * Prints step text.
     *
     * @param string        $stepType
     * @param string        $stepText
     */
    private function print_text(Output_Printer $printer, $step_type, $step_text, Step_Result $result): void
    {
        if ($result instanceof Defined_Step_Result && $result->get_step_definition()) {
            $definition = $result->get_step_definition();
            $step_text = $this->text_painter->paint_text($step_text, $definition, $result);
        }
        $style = $this->result_converter->convert_result_to_string($result);
        $printer->write(sprintf('%s{+%s}%s %s{-%s}', $this->indent_text, $style, $step_type, $step_text, $style));
    }
    /**
     * Prints step multiline arguments.
     *
     * @param ArgumentInterface[] $arguments
     */
    private function print_arguments(Formatter $formatter, array $arguments, Step_Result $result): void
    {
        $style = $this->result_converter->convert_result_to_string($result);
        foreach ($arguments as $argument) {
            $text = $this->get_argument_string($argument, !$formatter->get_parameter('multiline'));
            $indented_text = implode("\n", array_map($this->sub_indent(...), explode("\n", $text)));
            $formatter->get_output_printer()->writeln(sprintf('{+%s}%s{-%s}', $style, $indented_text, $style));
        }
    }
    /**
     * Prints step output (if has one).
     */
    private function print_std_out(Output_Printer $printer, Step_Result $result): void
    {
        if (!$result instanceof Executed_Step_Result || null === $result->get_call_result()->get_std_out()) {
            return;
        }
        $call_result = $result->get_call_result();
        $indented_text = $this->sub_indent_text;
        $pad = fn($line): string => sprintf('%s│ {+stdout}%s{-stdout}', $indented_text, $line);
        $printer->writeln(implode("\n", array_map($pad, explode("\n", (string) $call_result->get_std_out()))));
    }
    /**
     * Prints step exception (if has one).
     */
    private function print_exception(Output_Printer $printer, Step_Result $result): void
    {
        $style = $this->result_converter->convert_result_to_string($result);
        if (!$result instanceof Exception_Result || !$result->has_exception()) {
            return;
        }
        $text = $this->exception_presenter->present_exception($result->get_exception());
        $indented_text = implode("\n", array_map($this->sub_indent(...), explode("\n", $text)));
        $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $indented_text, $style));
    }
    /**
     * Returns argument string for provided argument.
     */
    private function get_argument_string(Argument_Interface $argument, bool $collapse = false): string
    {
        if ($collapse) {
            return '...';
        }
        if ($argument instanceof Py_String_Node) {
            return '"""' . "\n" . $argument . "\n" . '"""';
        }
        if ($argument instanceof Table_Node) {
            return (string) $argument;
        }
        return '';
    }
    /**
     * Indents text to the subIndentation level.
     */
    private function sub_indent(string $text): string
    {
        return $this->sub_indent_text . $text;
    }
}