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

use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
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
    public function print_header(Formatter $formatter, FeatureNode $feature, ExampleNode $example);

    /**
     * Prints example footer using provided printer.
     */
    public function print_footer(Formatter $formatter, Test_Result $result);
}
