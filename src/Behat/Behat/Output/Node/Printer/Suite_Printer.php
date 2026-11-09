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

use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Suite\Suite;

/**
 * Prints suite headers and footers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Suite_Printer
{
    /**
     * Prints suite header using provided formatter.
     */
    public function print_header(Formatter $formatter, Suite $suite);

    /**
     * Prints suite footer using provided printer.
     */
    public function print_footer(Formatter $formatter, Suite $suite);
}
