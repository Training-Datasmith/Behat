<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Setup\Suite_Setup;
/**
 * Configures provided suites using registered suite setups.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Suite_Bootstrapper
{
    /**
     * @var SuiteSetup[]
     */
    private array $setups = [];
    /**
     * Registers suite setup.
     */
    public function register_suite_setup(Suite_Setup $setup): void
    {
        $this->setups[] = $setup;
    }
    /**
     * Bootstraps provided suites using registered setups.
     *
     * @param Suite[] $suites
     */
    public function bootstrap_suites(array $suites): void
    {
        array_map($this->bootstrap_suite(...), $suites);
    }
    /**
     * Bootstraps provided suite using registered setup.
     */
    public function bootstrap_suite(Suite $suite): void
    {
        foreach ($this->setups as $setup) {
            if ($setup->supports_suite($suite)) {
                $setup->setup_suite($suite);
            }
        }
    }
}