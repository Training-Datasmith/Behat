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
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Call\Call_Result;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Hook\Call\Runtime_Hook;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prints hooks in a pretty fashion.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Setup_Printer implements Setup_Printer
{
    /**
     * @var string
     */
    private $indent_text;

    /**
     * Initializes printer.
     *
     * @param int  $indentation
     */
    public function __construct(private readonly Result_To_String_Converter $result_converter, private readonly Exception_Presenter $exception_presenter, $indentation = 0, private $newline_before = false, private $newline_after = false)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
    }

    public function print_setup(Formatter $formatter, Setup $setup): void
    {
        if (!$setup instanceof Hooked_Setup) {
            return;
        }
        foreach ($setup->get_hook_call_results() as $call_result) {
            $this->print_setup_hook_call_result($formatter->get_output_printer(), $call_result);
        }
    }

    public function print_teardown(Formatter $formatter, Teardown $teardown): void
    {
        if (!$teardown instanceof Hooked_Teardown) {
            return;
        }
        foreach ($teardown->get_hook_call_results() as $call_result) {
            $this->print_teardown_hook_call_result($formatter->get_output_printer(), $call_result);
        }
    }

    /**
     * Prints setup hook call result.
     */
    private function print_setup_hook_call_result(Output_Printer $printer, Call_Result $call_result): void
    {
        if (!$call_result->has_std_out() && !$call_result->has_exception()) {
            return;
        }
        $result_code = $call_result->has_exception() ? Test_Result::FAILED : Test_Result::PASSED;
        $style = $this->result_converter->convert_result_code_to_string($result_code);
        $hook = $call_result->get_call()->get_callee();
        $path = $hook->get_path();
        assert($hook instanceof Runtime_Hook);
        $printer->writeln(sprintf('%s┌─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $this->indent_text, $style, $hook, $style, $path));
        $printer->writeln(sprintf('%s│', $this->indent_text));
        $this->print_hook_call_std_out($printer, $call_result, $this->indent_text);
        $this->print_hook_call_exception($printer, $call_result, $this->indent_text);
        if ($this->newline_before) {
            $printer->writeln();
        }
    }

    /**
     * Prints teardown hook call result.
     */
    private function print_teardown_hook_call_result(Output_Printer $printer, Call_Result $call_result): void
    {
        if (!$call_result->has_std_out() && !$call_result->has_exception()) {
            return;
        }
        $result_code = $call_result->has_exception() ? Test_Result::FAILED : Test_Result::PASSED;
        $style = $this->result_converter->convert_result_code_to_string($result_code);
        $hook = $call_result->get_call()->get_callee();
        $path = $hook->get_path();
        $printer->writeln(sprintf('%s│', $this->indent_text));
        $this->print_hook_call_std_out($printer, $call_result, $this->indent_text);
        $this->print_hook_call_exception($printer, $call_result, $this->indent_text);
        assert($hook instanceof Runtime_Hook);
        $printer->writeln(sprintf('%s└─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $this->indent_text, $style, $hook, $style, $path));
        if ($this->newline_after) {
            $printer->writeln();
        }
    }

    /**
     * Prints hook call output (if has some).
     */
    private function print_hook_call_std_out(Output_Printer $printer, Call_Result $call_result, $indent_text): void
    {
        if (!$call_result->has_std_out()) {
            return;
        }
        $pad = fn ($line): string => sprintf('%s│  {+stdout}%s{-stdout}', $indent_text, $line);
        $printer->writeln(implode("\n", array_map($pad, explode("\n", (string) $call_result->get_std_out()))));
        $printer->writeln(sprintf('%s│', $indent_text));
    }

    /**
     * Prints hook call exception (if has some).
     */
    private function print_hook_call_exception(Output_Printer $printer, Call_Result $call_result, $indent_text): void
    {
        if (!$call_result->has_exception()) {
            return;
        }
        $pad = fn ($l): string => sprintf('%s╳  {+exception}%s{-exception}', $indent_text, $l);
        $exception = $this->exception_presenter->present_exception($call_result->get_exception());
        $printer->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        $printer->writeln(sprintf('%s│', $indent_text));
    }
}
