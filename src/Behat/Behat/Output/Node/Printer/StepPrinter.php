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

use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\Scenario_Like_Interface as Scenario;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Output\Formatter;
/**
 * Prints step with optional results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Step_Printer
{
    /**
     * Prints step using provided printer.
     */
    public function print_step(Formatter $formatter, Scenario $scenario, Step_Node $step, Step_Result $result);
}