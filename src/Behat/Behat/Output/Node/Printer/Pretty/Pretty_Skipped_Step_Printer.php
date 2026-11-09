<?php

declare(strict_types=1);
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
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Prints steps as skipped.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Skipped_Step_Printer implements Step_Printer
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
     */
    public function __construct(private readonly Step_Text_Painter $text_painter, private readonly Result_To_String_Converter $result_converter, private readonly Pretty_Path_Printer $path_printer, $indentation = 4, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }

    public function print_step(Formatter $formatter, Scenario $scenario, StepNode $step, Step_Result $result): void
    {
        $this->print_text($formatter->get_output_printer(), $step->getKeyword(), $step->getText(), $result);
        $this->path_printer->print_step_path($formatter, $scenario, $step, $result, mb_strlen($this->indent_text, 'utf8'));
        $this->print_arguments($formatter, $step->getArguments());
    }

    /**
     * Prints step text.
     */
    private function print_text(Output_Printer $printer, $step_type, $step_text, Step_Result $result): void
    {
        $style = $this->result_converter->convert_result_code_to_string(Test_Result::SKIPPED);
        if ($result instanceof Defined_Step_Result && $result->get_step_definition()) {
            $definition = $result->get_step_definition();
            $step_text = $this->text_painter->paint_text($step_text, $definition, new Integer_Test_Result(Test_Result::SKIPPED));
        }
        $printer->write(sprintf('%s{+%s}%s %s{-%s}', $this->indent_text, $style, $step_type, $step_text, $style));
    }

    /**
     * Prints step multiline arguments.
     *
     * @param ArgumentInterface[] $arguments
     */
    private function print_arguments(Formatter $formatter, array $arguments): void
    {
        $style = $this->result_converter->convert_result_code_to_string(Test_Result::SKIPPED);
        foreach ($arguments as $argument) {
            $text = $this->get_argument_string($argument, !$formatter->get_parameter('multiline'));
            $indented_text = implode("\n", array_map($this->sub_indent(...), explode("\n", $text)));
            $formatter->get_output_printer()->writeln(sprintf('{+%s}%s{-%s}', $style, $indented_text, $style));
        }
    }

    /**
     * Returns argument string for provided argument.
     */
    private function get_argument_string(ArgumentInterface $argument, bool $collapse = false): string
    {
        if ($collapse) {
            return '...';
        }
        if ($argument instanceof PyStringNode) {
            return '"""' . "\n" . $argument . "\n" . '"""';
        }
        if ($argument instanceof TableNode) {
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
