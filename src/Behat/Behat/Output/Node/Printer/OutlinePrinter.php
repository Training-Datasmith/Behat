<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer;

use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Outline_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints outline headers and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Outline_Printer
{
    /**
     * Prints outline header using provided printer.
     */
    public function print_header(Formatter $formatter, Feature_Node $feature, Outline_Node $outline);
    /**
     * Prints outline footer using provided printer.
     */
    public function print_footer(Formatter $formatter, Test_Result $result);
}