<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\Progress;

use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Behat progress step printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Progress_Step_Printer implements Step_Printer
{
    private int $steps_printed = 0;
    private bool $has_printed_output = false;
    /**
     * Initializes printer.
     */
    public function __construct(private readonly Result_To_String_Converter $result_converter)
    {
    }
    public function print_step(Formatter $formatter, Scenario $scenario, Step_Node $step, Step_Result $result): void
    {
        $printer = $formatter->get_output_printer();
        $style = $this->result_converter->convert_result_to_string($result);
        // After printing any output, we need to print a new line before continuing
        // to print the "dots" of the progress
        if ($this->has_printed_output) {
            $printer->writeln('');
            $this->has_printed_output = false;
        }
        switch ($result->get_result_code()) {
            case Test_Result::PASSED:
                $printer->write("{+{$style}}.{-{$style}}");
                break;
            case Test_Result::SKIPPED:
                $printer->write("{+{$style}}-{-{$style}}");
                break;
            case Test_Result::PENDING:
                $printer->write("{+{$style}}P{-{$style}}");
                break;
            case Test_Result::UNDEFINED:
                $printer->write("{+{$style}}U{-{$style}}");
                break;
            case Test_Result::FAILED:
                $printer->write("{+{$style}}F{-{$style}}");
                break;
        }
        $show_output = $formatter->get_parameter(Show_Output_Option::OPTION_NAME);
        if ($show_output === Show_Output_Option::Yes || $show_output === Show_Output_Option::OnFail && !$result->is_passed()) {
            $this->print_std_out($formatter->get_output_printer(), $result);
        }
        if (++$this->steps_printed % 70 == 0) {
            $printer->writeln(' ' . $this->steps_printed);
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
        $printer->writeln("\n" . $result->get_step_definition()->get_path() . ':');
        $call_result = $result->get_call_result();
        $pad = fn(string $line): string => sprintf('  | {+stdout}%s{-stdout}', $line);
        $printer->write(implode("\n", array_map($pad, explode("\n", (string) $call_result->get_std_out()))));
        $this->has_printed_output = true;
    }
}