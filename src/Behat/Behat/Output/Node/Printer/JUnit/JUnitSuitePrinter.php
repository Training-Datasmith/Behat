<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\J_Unit;

use Behat\Behat\Output\Node\Printer\Suite_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Suite\Suite;
/**
 * Creates new JUnit report file.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final readonly class J_Unit_Suite_Printer implements Suite_Printer
{
    public function __construct(private ?Phase_Statistics $statistics = null)
    {
    }
    public function print_header(Formatter $formatter, Suite $suite): void
    {
        if ($this->statistics instanceof Phase_Statistics) {
            $this->statistics->reset();
        }
        /** @var JUnitOutputPrinter $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $output_printer->create_new_file($suite->get_name());
    }
    public function print_footer(Formatter $formatter, Suite $suite): void
    {
        $formatter->get_output_printer()->flush();
    }
}