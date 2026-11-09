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

use Behat\Behat\Output\Node\Printer\Example_Printer;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Prints example header (usually simply an example row) and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pretty_Example_Printer implements Example_Printer
{
    /**
     * @var string
     */
    private $indent_text;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     */
    public function __construct(private readonly Pretty_Path_Printer $path_printer, $indentation = 6)
    {
        $this->indent_text = str_repeat(' ', intval($indentation));
    }

    public function print_header(Formatter $formatter, FeatureNode $feature, ExampleNode $example): void
    {
        $this->print_title($formatter->get_output_printer(), $example);
        $this->path_printer->print_scenario_path($formatter, $feature, $example, mb_strlen($this->indent_text, 'utf8'));
    }

    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
    }

    /**
     * Prints example title.
     */
    private function print_title(Output_Printer $printer, ExampleNode $example): void
    {
        $printer->write(sprintf('%s%s', $this->indent_text, $example->getTitle()));
    }
}
