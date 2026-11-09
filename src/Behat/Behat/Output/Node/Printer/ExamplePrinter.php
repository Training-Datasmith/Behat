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

use Behat\Gherkin\Node\Example_Node;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints example headers and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Example_Printer
{
    /**
     * Prints example header using provided printer.
     */
    public function print_header(Formatter $formatter, Feature_Node $feature, Example_Node $example);
    /**
     * Prints example footer using provided printer.
     */
    public function print_footer(Formatter $formatter, Test_Result $result);
}