<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Exception\Environment_Build_Exception;
use Behat\Testwork\Environment\Exception\Environment_Isolation_Exception;
use Behat\Testwork\Environment\Handler\Environment_Handler;
use Behat\Testwork\Environment\Reader\Environment_Reader;
use Behat\Testwork\Suite\Suite;

/**
 * Builds, isolates and reads environments using registered handlers and readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Environment_Manager
{
    /**
     * @var Environment_Handler[]
     */
    private array $handlers = [];
    /**
     * @var Environment_Reader[]
     */
    private array $readers = [];

    /**
     * Registers environment handler.
     */
    public function register_environment_handler(Environment_Handler $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * Registers environment reader.
     */
    public function register_environment_reader(Environment_Reader $reader): void
    {
        $this->readers[] = $reader;
    }

    /**
     * Builds new environment for provided test suite.
     *
     * @return Environment
     *
     * @throws Environment_Build_Exception
     */
    public function build_environment(Suite $suite)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports_suite($suite)) {
                return $handler->build_environment($suite);
            }
        }
        throw new Environment_Build_Exception(sprintf('None of the registered environment handlers seem to support `%s` suite.', $suite->get_name()), $suite);
    }

    /**
     * Creates new isolated test environment using built one.
     *
     * @return Environment
     *
     * @throws Environment_Isolation_Exception If appropriate environment handler is not found
     */
    public function isolate_environment(Environment $environment, $test_subject = null)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports_environment_and_subject($environment, $test_subject)) {
                return $handler->isolate_environment($environment, $test_subject);
            }
        }
        throw new Environment_Isolation_Exception(sprintf('None of the registered environment handlers seem to support `%s` environment.', $environment::class), $environment, $test_subject);
    }

    /**
     * Reads all callees from environment using registered environment readers.
     *
     * @return Callee[]
     */
    public function read_environment_callees(Environment $environment): array
    {
        $callees = [];
        foreach ($this->readers as $reader) {
            if ($reader->supports_environment($environment)) {
                $callees = array_merge($callees, $reader->read_environment_callees($environment));
            }
        }

        return $callees;
    }
}
