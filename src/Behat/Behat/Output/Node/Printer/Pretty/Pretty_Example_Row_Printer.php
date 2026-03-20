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

use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Output\Node\Printer\Example_Row_Printer;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Example_Node;
use Behat\Gherkin\Node\Outline_Node;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Exception_Result;
use Behat\Testwork\Tester\Result\Test_Results;
/**
 * Prints example results in form of a table row.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Example_Row_Printer implements Example_Row_Printer
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
    public function __construct(private readonly Result_To_String_Converter $result_converter, private readonly Exception_Presenter $exception_presenter, private readonly Translator_Interface $translator, $indentation = 6, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }
    public function print_example_row(Formatter $formatter, Outline_Node $outline, Example_Node $example, array $events): void
    {
        $row_num = array_search($example, $outline->get_examples()) + 1;
        $wrapper = $this->get_wrapper_closure($outline, $example, $events);
        $row = $outline->get_example_table()->get_row_as_string_with_wrapped_values($row_num, $wrapper);
        $formatter->get_output_printer()->writeln(sprintf('%s%s', $this->indent_text, $row));
        $this->print_step_exceptions_and_std_out($formatter->get_output_printer(), $events);
    }
    /**
     * Creates wrapper-closure for the example table.
     *
     * @param AfterStepTested[] $stepEvents
     *
     * @return callable
     */
    private function get_wrapper_closure(Outline_Node $outline, Example_Node $example, array $step_events)
    {
        $result_converter = $this->result_converter;
        return function ($value, $column) use ($outline, $example, $step_events, $result_converter): string {
            $results = [];
            foreach ($step_events as $event) {
                $index = array_search($event->get_step(), $example->get_steps());
                $header = $outline->get_example_table()->get_row(0);
                $steps = $outline->get_steps();
                $outline_step_text = $steps[$index]->get_text();
                if (str_contains($outline_step_text, '<' . $header[$column] . '>')) {
                    $results[] = $event->get_test_result();
                }
            }
            $result = new Test_Results($results);
            $style = $result_converter->convert_result_to_string($result);
            return sprintf('{+%s}%s{-%s}', $style, $value, $style);
        };
    }
    /**
     * Prints step events exceptions (if has some).
     *
     * @param AfterTested[] $events
     */
    private function print_step_exceptions_and_std_out(Output_Printer $printer, array $events): void
    {
        foreach ($events as $event) {
            $step_result = $event->get_test_result();
            assert($step_result instanceof Step_Result);
            $this->print_step_std_out($printer, $step_result);
            $this->print_step_exception($printer, $event);
        }
    }
    /**
     * Prints step exception (if has one).
     */
    private function print_step_exception(Output_Printer $printer, After_Tested $event): void
    {
        $result = $event->get_test_result();
        $style = $this->result_converter->convert_result_to_string($result);
        if (!$result instanceof Exception_Result || !$result->has_exception()) {
            return;
        }
        if ($event instanceof After_Step_Tested) {
            $title = $this->translator->trans('failed_step_title', [], 'output');
            $step = $event->get_step();
            $printer->writeln(sprintf('{+%s}%s%s %s %s{-%s}', $style, $this->sub_indent_text, $title, $step->get_keyword(), $step->get_text(), $style));
        }
        $text = $this->exception_presenter->present_exception($result->get_exception());
        $indented_text = implode("\n", array_map($this->sub_indent(...), explode("\n", $text)));
        $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $indented_text, $style));
    }
    /**
     * Prints step output (if has one).
     */
    private function print_step_std_out(Output_Printer $printer, Step_Result $result): void
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
     * Indents text to the subIndentation level.
     */
    private function sub_indent(string $text): string
    {
        return $this->sub_indent_text . $text;
    }
}