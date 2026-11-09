<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Exception\Suite_Generation_Exception;
use Behat\Testwork\Suite\Generator\Suite_Generator;

/**
 * Acts like a suite repository by auto-generating suites for registered suite configurations using registered
 * generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Suite_Registry implements Suite_Repository
{
    private bool $suites_generated = false;
    /**
     * @var Suite_Generator[]
     */
    private array $generators = [];
    private array $suite_configurations = [];
    /**
     * @var Suite[]
     */
    private array $suites = [];

    /**
     * Registers suite generator.
     */
    public function register_suite_generator(Suite_Generator $generator): void
    {
        $this->generators[] = $generator;
        $this->suites_generated = false;
    }

    /**
     * Registers suite using provided name, type & parameters.
     *
     * @param string $name
     * @param string $type
     *
     * @throws Suite_Configuration_Exception
     */
    public function register_suite_configuration($name, $type, array $settings): void
    {
        if (isset($this->suite_configurations[$name])) {
            throw new Suite_Configuration_Exception(sprintf('Suite configuration for a suite "%s" is already registered.', $name), $name);
        }
        $this->suite_configurations[$name] = [$type, $settings];
        $this->suites_generated = false;
    }

    /**
     * Returns all available suites.
     *
     * @return Suite[]
     */
    public function get_suites()
    {
        if ($this->suites_generated) {
            return $this->suites;
        }
        $this->suites = [];
        foreach ($this->suite_configurations as $name => $configuration) {
            [$type, $settings] = $configuration;
            $this->suites[] = $this->generate_suite($name, $type, $settings);
        }
        $this->suites_generated = true;

        return $this->suites;
    }

    /**
     * Generates suite using registered generators.
     *
     * @throws Suite_Generation_Exception If no appropriate generator found
     */
    private function generate_suite(string $name, ?string $type, array $settings): Suite
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supports_type_and_settings($type, $settings)) {
                continue;
            }

            return $generator->generate_suite($name, $settings);
        }
        throw new Suite_Generation_Exception(sprintf('Can not find suite generator for a suite `%s` of type `%s`.', $name, $type), $name);
    }
}
