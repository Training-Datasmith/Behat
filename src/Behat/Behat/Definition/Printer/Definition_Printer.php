<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Printer;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Suite\Suite;

/**
 * Prints provided definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Definition_Printer
{
    /**
     * Prints definition.
     *
     * @param Definition[] $definitions
     */
    public function print_definitions(Suite $suite, $definitions);
}
