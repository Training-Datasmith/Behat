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

use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Output\Formatter;

/**
 * Prints exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Statistics_Printer
{
    /**
     * Prints test suite statistics after run.
     */
    public function print_statistics(Formatter $formatter, Statistics $statistics);
}
