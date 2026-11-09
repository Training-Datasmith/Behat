<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Tester\Setup;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents hooked test setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class HookedSetup implements Setup
{
    /**
     * Initializes setup.
     */
    public function __construct(
        private Setup $setup,
        private CallResults $hookCallResults,
    ) {
    }

    public function isSuccessful()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return false;
        }

        return $this->setup->isSuccessful();
    }

    public function hasOutput(): bool
    {
        if ($this->hookCallResults->hasStdOuts()) {
            return true;
        }
        return $this->hookCallResults->hasExceptions();
    }

    /**
     * Returns hook call results.
     */
    public function getHookCallResults(): CallResults
    {
        return $this->hookCallResults;
    }
}
