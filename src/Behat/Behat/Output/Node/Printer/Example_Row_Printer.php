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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Formatter;

/**
 * Prints outline example row results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Example_Row_Printer
{
    /**
     * Prints example row result using provided printer.
     *
     * @param After_Step_Tested[] $events
     */
    public function print_example_row(Formatter $formatter, OutlineNode $outline, ExampleNode $example, array $events);
}
