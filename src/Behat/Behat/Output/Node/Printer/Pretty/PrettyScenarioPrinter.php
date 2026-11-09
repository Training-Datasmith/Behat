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

use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Tagged_Node_Interface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints scenario headers (with tags, keyword and long title) and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Scenario_Printer implements Scenario_Printer
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
    public function __construct(private readonly Pretty_Path_Printer $path_printer, $indentation = 2, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }
    public function print_header(Formatter $formatter, Feature_Node $feature, Scenario $scenario): void
    {
        if ($scenario instanceof Tagged_Node_Interface) {
            $this->print_tags($formatter->get_output_printer(), $scenario->get_tags());
        }
        $this->print_keyword($formatter->get_output_printer(), $scenario->get_keyword());
        $this->print_title($formatter->get_output_printer(), $scenario->get_title());
        $this->path_printer->print_scenario_path($formatter, $feature, $scenario, mb_strlen($this->indent_text, 'utf8'));
        $this->print_description($formatter->get_output_printer(), $scenario->get_title());
    }
    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $formatter->get_output_printer()->writeln();
    }
    /**
     * Prints scenario tags.
     *
     * @param string[]      $tags
     */
    private function print_tags(Output_Printer $printer, array $tags): void
    {
        if (!count($tags)) {
            return;
        }
        $tags = array_map($this->prepend_tag_with_tag_sign(...), $tags);
        $printer->writeln(sprintf('%s{+tag}%s{-tag}', $this->indent_text, implode(' ', $tags)));
    }
    /**
     * Prints scenario keyword.
     *
     * @param string        $keyword
     */
    private function print_keyword(Output_Printer $printer, $keyword): void
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indent_text, $keyword));
    }
    /**
     * Prints scenario title (first line of long title).
     *
     * @param string|null   $longTitle
     */
    private function print_title(Output_Printer $printer, $long_title): void
    {
        $description = explode("\n", $long_title ?? '');
        $title = array_shift($description);
        if ('' !== $title) {
            $printer->write(sprintf(' %s', $title));
        }
    }
    /**
     * Prints scenario description (other lines of long title).
     *
     * @param string|null   $longTitle
     */
    private function print_description(Output_Printer $printer, $long_title): void
    {
        $lines = explode("\n", $long_title ?? '');
        array_shift($lines);
        foreach ($lines as $line) {
            $printer->writeln(sprintf('%s%s', $this->sub_indent_text, $line));
        }
    }
    /**
     * Prepends tags string with tag-sign.
     */
    private function prepend_tag_with_tag_sign(string $tag): string
    {
        if (str_starts_with($tag, '@')) {
            return $tag;
        }
        // The legacy mode of the behat/gherkin parser is trimming the `@` from tags so we need to re-add it for pretty-printing
        return '@' . $tag;
    }
}