<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Tester\Setup;

use Behat\Testwork\Call\Call_Results;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents hooked test setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hooked_Setup implements Setup
{
    /**
     * Initializes setup.
     */
    public function __construct(private Setup $setup, private Call_Results $hook_call_results)
    {
    }

    public function is_successful()
    {
        if ($this->hook_call_results->has_exceptions()) {
            return false;
        }

        return $this->setup->is_successful();
    }

    public function has_output(): bool
    {
        if ($this->hook_call_results->has_std_outs()) {
            return true;
        }

        return $this->hook_call_results->has_exceptions();
    }

    /**
     * Returns hook call results.
     */
    public function get_hook_call_results(): Call_Results
    {
        return $this->hook_call_results;
    }
}
