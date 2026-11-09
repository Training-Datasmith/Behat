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
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Behat setup printer interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Setup_Printer
{
    /**
     * Prints setup state.
     */
    public function print_setup(Formatter $formatter, Setup $setup);

    /**
     * Prints teardown state.
     */
    public function print_teardown(Formatter $formatter, Teardown $teardown);
}
