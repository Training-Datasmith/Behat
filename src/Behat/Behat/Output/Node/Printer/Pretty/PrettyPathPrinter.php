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

use Behat\Behat\Output\Node\Printer\Helper\Width_Calculator;
use Behat\Behat\Tester\Result\Defined_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
/**
 * Prints paths for scenarios, examples, backgrounds and steps.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Pretty_Path_Printer
{
    private Configurable_Path_Printer $configurable_path_printer;
    /**
     * Initializes printer.
     */
    public function __construct(private Width_Calculator $width_calculator, string $base_path, ?Configurable_Path_Printer $configurable_path_printer = null)
    {
        $this->configurable_path_printer = $configurable_path_printer ?? new Configurable_Path_Printer($base_path, printAbsolutePaths: false);
    }
    /**
     * Prints scenario path comment.
     *
     * @param int $indentation
     */
    public function print_scenario_path(Formatter $formatter, Feature_Node $feature, Scenario $scenario, $indentation): void
    {
        $printer = $formatter->get_output_printer();
        if (!$formatter->get_parameter('paths')) {
            $printer->writeln();
            return;
        }
        $file_and_line = $this->configurable_path_printer->process_paths_in_text(sprintf('%s:%s', $feature->get_file(), $scenario->get_line()));
        $header_width = $this->width_calculator->calculate_scenario_header_width($scenario, $indentation);
        $scenario_width = $this->width_calculator->calculate_scenario_width($scenario, $indentation, 2);
        $spacing = str_repeat(' ', max(0, $scenario_width - $header_width));
        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $file_and_line));
    }
    /**
     * Prints step path comment.
     *
     * @param int $indentation
     */
    public function print_step_path(Formatter $formatter, Scenario $scenario, Step_Node $step, Step_Result $result, $indentation): void
    {
        $printer = $formatter->get_output_printer();
        if (!$result instanceof Defined_Step_Result || !$result->get_step_definition() || !$formatter->get_parameter('paths')) {
            $printer->writeln();
            return;
        }
        $text_width = $this->width_calculator->calculate_step_width($step, $indentation);
        $scenario_width = $this->width_calculator->calculate_scenario_width($scenario, $indentation - 2, 2);
        $this->print_defined_step_path($printer, $result, $scenario_width, $text_width);
    }
    /**
     * Prints defined step path.
     *
     * @param int $scenarioWidth
     */
    private function print_defined_step_path(Output_Printer $printer, Defined_Step_Result $result, $scenario_width, int $step_width): void
    {
        $path = $result->get_step_definition()->get_path();
        $spacing = str_repeat(' ', max(0, $scenario_width - $step_width));
        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $path));
    }
}