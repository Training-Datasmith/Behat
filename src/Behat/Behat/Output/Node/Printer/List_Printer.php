<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Hook\Scope\After_Feature_Scope;
use Behat\Behat\Hook\Scope\After_Scenario_Scope;
use Behat\Behat\Hook\Scope\After_Step_Scope;
use Behat\Behat\Hook\Scope\Before_Feature_Scope;
use Behat\Behat\Hook\Scope\Before_Scenario_Scope;
use Behat\Behat\Hook\Scope\Before_Step_Scope;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Statistics\Hook_Stat;
use Behat\Behat\Output\Statistics\Scenario_Stat;
use Behat\Behat\Output\Statistics\Step_Stat;
use Behat\Behat\Output\Statistics\Step_Stat_V2;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Hook\Scope\After_Suite_Scope;
use Behat\Testwork\Hook\Scope\Before_Suite_Scope;
use Behat\Testwork\Hook\Scope\Hook_Scope;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Behat list printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class List_Printer
{
    private Configurable_Path_Printer $configurable_path_printer;

    public function __construct(private Result_To_String_Converter $result_converter, Exception_Presenter $exception_presenter, private Translator_Interface $translator, string $base_path, ?Configurable_Path_Printer $configurable_path_printer = null)
    {
        $this->configurable_path_printer = $configurable_path_printer ?? new Configurable_Path_Printer($base_path, print_absolute_paths: false);
    }

    /**
     * Prints scenarios list.
     *
     * @param string         $intro
     */
    public function print_scenarios_list(Output_Printer $printer, $intro, $result_code, array $scenario_stats, ?array $step_stats = null): void
    {
        if (!count($scenario_stats)) {
            return;
        }
        $style = $this->result_converter->convert_result_code_to_string($result_code);
        $intro = $this->translator->trans($intro, [], 'output');
        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($scenario_stats as $stat) {
            $path = $this->configurable_path_printer->process_paths_in_text((string) $stat);
            $path = $this->append_failing_step_text($step_stats, $path, $stat);
            $printer->writeln(sprintf('    {+%s}%s{-%s}', $style, $path, $style));
        }
        $printer->writeln();
    }

    /**
     * Prints step list.
     *
     * @param string     $intro
     */
    public function print_step_list(Output_Printer $printer, $intro, $result_code, array $step_stats, ?Show_Output_Option $show_output = Show_Output_Option::InSummary): void
    {
        if (!count($step_stats)) {
            return;
        }
        $style = $this->result_converter->convert_result_code_to_string($result_code);
        $intro = $this->translator->trans($intro, [], 'output');
        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($step_stats as $num => $step_stat) {
            if ($step_stat instanceof Step_Stat_V2) {
                $this->print_step_stat($printer, $num + 1, $step_stat, $style, $show_output);
            } elseif ($step_stat instanceof Step_Stat) {
                $this->print_stat($printer, $step_stat->getText(), $step_stat->get_path(), $style, $step_stat->get_std_out(), $step_stat->get_error(), $show_output);
            }
        }
    }

    /**
     * Prints failed hooks list.
     */
    public function print_failed_hooks_list(Output_Printer $printer, string $intro, array $failed_hook_stats, bool $simple = false): void
    {
        if (!count($failed_hook_stats)) {
            return;
        }
        $style = $this->result_converter->convert_result_code_to_string(Test_Result::FAILED);
        $intro = $this->translator->trans($intro, [], 'output');
        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($failed_hook_stats as $hook_stat) {
            $this->print_hook_stat($printer, $hook_stat, $style, $simple);
        }
        if ($simple) {
            $printer->writeln();
        }
    }

    /**
     * Prints hook stat.
     *
     * @deprecated Remove in 4.0
     */
    private function print_stat(Output_Printer $printer, string $name, string $path, string $style, ?string $std_out, ?string $error, ?Show_Output_Option $show_output): void
    {
        $path = $this->configurable_path_printer->process_paths_in_text($path);
        $printer->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $name, $style, $path));
        $pad = fn ($line): string => '      ' . $line;
        if (null !== $std_out && $show_output !== Show_Output_Option::No) {
            $pad_text = fn ($line): string => '      │ ' . $line;
            $std_out_string = array_map($pad_text, explode("\n", $std_out));
            $printer->writeln(implode("\n", $std_out_string));
        }
        if ($error) {
            $exception_string = implode("\n", array_map($pad, explode("\n", $error)));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exception_string, $style));
        }
        $printer->writeln();
    }

    /**
     * Prints hook stat.
     */
    private function print_hook_stat(Output_Printer $printer, Hook_Stat $hook_stat, string $style, bool $simple): void
    {
        $location = $this->get_location_from_scope($hook_stat->get_scope());
        $printer->writeln(sprintf('    {+%s}%s{-%s}%s {+comment}# %s{-comment}', $style, $hook_stat->get_name(), $style, $location ? " \"{$location}\"" : '', $this->configurable_path_printer->process_paths_in_text($hook_stat->get_path())));
        if ($simple) {
            return;
        }
        $pad = fn ($line): string => '      ' . $line;
        if (null !== $hook_stat->get_std_out()) {
            $pad_text = fn ($line): string => '      │ ' . $line;
            $std_out_string = array_map($pad_text, explode("\n", $hook_stat->get_std_out()));
            $printer->writeln(implode("\n", $std_out_string));
        }
        if ($hook_stat->get_error()) {
            $exception_string = implode("\n", array_map($pad, explode("\n", $hook_stat->get_error())));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exception_string, $style));
        }
        $printer->writeln();
    }

    private function print_step_stat(Output_Printer $printer, int $number, Step_Stat_V2 $stat, string $style, ?Show_Output_Option $show_output): void
    {
        $max_length = max(mb_strlen($stat->get_scenario_text(), 'utf8'), mb_strlen($stat->get_step_text(), 'utf8') + 2) + 1;
        $printer->writeln(sprintf('%03d {+%s}%s{-%s}%s{+comment}# %s{-comment}', $number, $style, $stat->get_scenario_text(), $style, str_pad(' ', $max_length - mb_strlen($stat->get_scenario_text(), 'utf8')), $this->configurable_path_printer->process_paths_in_text($stat->get_scenario_path())));
        $printer->writeln(sprintf('      {+%s}%s{-%s}%s{+comment}# %s{-comment}', $style, $stat->get_step_text(), $style, str_pad(' ', $max_length - mb_strlen($stat->get_step_text(), 'utf8') - 2), $this->configurable_path_printer->process_paths_in_text($stat->get_step_path())));
        $pad = fn ($line): string => '        ' . $line;
        if (null !== $stat->get_std_out() && $show_output !== Show_Output_Option::No) {
            $pad_text = fn ($line): string => '        │ ' . $line;
            $std_out_string = array_map($pad_text, explode("\n", $stat->get_std_out()));
            $printer->writeln(implode("\n", $std_out_string));
        }
        if ($stat->get_error()) {
            $exception_string = implode("\n", array_map($pad, explode("\n", $stat->get_error())));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exception_string, $style));
        }
        $printer->writeln();
    }

    private function append_failing_step_text(?array $step_stats, string $path, Scenario_Stat $scenario_stat): string
    {
        $found_step_stat = null;
        if (null === $step_stats) {
            return $path;
        }
        foreach ($step_stats as $step_stat) {
            // See https://github.com/Behat/Behat/pull/1615#pullrequestreview-2737706542
            // > although Statistics::getFailedSteps() is typed as returning Step_Stat[] for BC reasons,
            // > in practice we only ever create / register instances of Step_Stat_V2. And that has a
            // > getScenarioPath() which I think should always match the getPath() of Scenario_Stat.
            if ($step_stat instanceof Step_Stat_V2 && $step_stat->get_scenario_path() === $scenario_stat->get_path()) {
                $found_step_stat = $step_stat;
                break;
            }
        }
        if (!$found_step_stat instanceof Step_Stat_V2) {
            return $path;
        }
        $step_line = $this->extract_line_number($found_step_stat->get_step_path());
        if ($step_line !== null) {
            $line_number = $this->translator->trans('on_line_number', ['%line%' => $step_line], 'output');
            $line_helper = ' (' . $line_number . ')';
        } else {
            $line_helper = '';
        }

        return $path . $line_helper;
    }

    private function extract_line_number(string $path): ?string
    {
        $last_colon_pos = strrpos($path, ':');
        if (false === $last_colon_pos) {
            return null;
        }

        return substr($path, $last_colon_pos + 1);
    }

    private function get_location_from_scope(?Hook_Scope $scope): ?string
    {
        if ($scope instanceof Hook_Scope) {
            return match (true) {
                $scope instanceof Before_Suite_Scope, $scope instanceof After_Suite_Scope => $scope->get_suite()->get_name(),
                $scope instanceof Before_Feature_Scope, $scope instanceof After_Feature_Scope => $this->configurable_path_printer->process_paths_in_text($scope->get_feature()->getFile()),
                $scope instanceof Before_Scenario_Scope, $scope instanceof After_Scenario_Scope => $this->configurable_path_printer->process_paths_in_text($scope->get_feature()->getFile() . ':' . $scope->get_scenario()->getLine()),
                $scope instanceof Before_Step_Scope, $scope instanceof After_Step_Scope => $this->configurable_path_printer->process_paths_in_text($scope->get_feature()->getFile() . ':' . $scope->get_step()->getLine()),
                default => null,
            };
        }

        return null;
    }
}
