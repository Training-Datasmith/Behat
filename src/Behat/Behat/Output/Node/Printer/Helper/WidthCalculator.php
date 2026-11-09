<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Gherkin\Node\Example_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
/**
 * Calculates width of scenario. Width of scenario = max width of scenario title and scenario step texts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Width_Calculator
{
    /**
     * Calculates scenario width.
     *
     * @param int $indentation
     * @param int $subIndentation
     *
     * @return int
     */
    public function calculate_scenario_width(Scenario $scenario, $indentation, $sub_indentation)
    {
        $length = $this->calculate_scenario_header_width($scenario, $indentation);
        foreach ($scenario->get_steps() as $step) {
            $step_length = $this->calculate_step_width($step, $indentation + $sub_indentation);
            $length = max($length, $step_length);
        }
        return $length;
    }
    /**
     * Calculates outline examples width.
     *
     * @param int $indentation
     * @param int $subIndentation
     *
     * @return int
     */
    public function calculate_example_width(Example_Node $example, $indentation, $sub_indentation)
    {
        $length = $this->calculate_scenario_header_width($example, $indentation);
        foreach ($example->get_steps() as $step) {
            $step_length = $this->calculate_step_width($step, $indentation + $sub_indentation);
            $length = max($length, $step_length);
        }
        return $length;
    }
    /**
     * Calculates scenario header width.
     *
     * @param int $indentation
     */
    public function calculate_scenario_header_width(Scenario $scenario, $indentation): int
    {
        $indent_text = str_repeat(' ', intval($indentation));
        if ($scenario instanceof Example_Node) {
            $header = sprintf('%s%s', $indent_text, $scenario->get_title());
        } else {
            $title = $scenario->get_title();
            $lines = explode("\n", $title ?? '');
            $header = sprintf('%s%s: %s', $indent_text, $scenario->get_keyword(), array_shift($lines));
        }
        return mb_strlen(rtrim($header), 'utf8');
    }
    /**
     * Calculates step width.
     *
     * @param int $indentation
     */
    public function calculate_step_width(Step_Node $step, $indentation): int
    {
        $indent_text = str_repeat(' ', intval($indentation));
        $text = sprintf('%s%s %s', $indent_text, $step->get_keyword(), $step->get_text());
        return mb_strlen($text, 'utf8');
    }
}