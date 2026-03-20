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

use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints feature header and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Feature_Printer implements Feature_Printer
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
    public function __construct($indentation = 0, $sub_indentation = 2)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
        $this->sub_indent_text = $this->indent_text . str_repeat(' ', intval($sub_indentation));
    }
    public function print_header(Formatter $formatter, Feature_Node $feature): void
    {
        $this->print_tags($formatter->get_output_printer(), $feature->get_tags());
        $this->print_title($formatter->get_output_printer(), $feature);
        $this->print_description($formatter->get_output_printer(), $feature);
    }
    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
    }
    /**
     * Prints feature tags.
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
     * Prints feature title using provided printer.
     */
    private function print_title(Output_Printer $printer, Feature_Node $feature): void
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indent_text, $feature->get_keyword()));
        if ($title = $feature->get_title()) {
            $printer->write(sprintf(' %s', $title));
        }
        $printer->writeln();
    }
    /**
     * Prints feature description using provided printer.
     */
    private function print_description(Output_Printer $printer, Feature_Node $feature): void
    {
        if (!$feature->get_description()) {
            $printer->writeln();
            return;
        }
        foreach (explode("\n", $feature->get_description()) as $description_line) {
            $printer->writeln(sprintf('%s%s', $this->sub_indent_text, $description_line));
        }
        $printer->writeln();
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