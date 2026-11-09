<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Setup;

/**
 * Represents successful teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Successful_Teardown implements Teardown
{
    public function is_successful(): bool
    {
        return true;
    }

    public function has_output(): bool
    {
        return false;
    }
}
