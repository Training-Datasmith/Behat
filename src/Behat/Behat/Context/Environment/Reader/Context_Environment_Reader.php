<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Reader;

use Behat\Behat\Context\Environment\Context_Environment;
use Behat\Behat\Context\Reader\Context_Reader;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Exception\Environment_Read_Exception;
use Behat\Testwork\Environment\Reader\Environment_Reader;

/**
 * Reads context-based environment callees using registered context loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Environment_Reader implements Environment_Reader
{
    /**
     * @var Context_Reader[]
     */
    private array $context_readers = [];

    /**
     * Registers context loader.
     */
    public function register_context_reader(Context_Reader $context_reader): void
    {
        $this->context_readers[] = $context_reader;
    }

    public function supports_environment(Environment $environment): bool
    {
        return $environment instanceof Context_Environment;
    }

    /**
     * @return list<Callee>
     */
    public function read_environment_callees(Environment $environment): array
    {
        if (!$environment instanceof Context_Environment) {
            throw new Environment_Read_Exception(sprintf('Context_Environment_Reader does not support `%s` environment.', $environment::class), $environment);
        }
        $callees = [];
        foreach ($environment->get_context_classes() as $context_class) {
            $callees = array_merge($callees, $this->read_context_callees($environment, $context_class));
        }

        return $callees;
    }

    /**
     * Reads callees from a specific suite's context.
     *
     * @return list<Callee>
     */
    private function read_context_callees(Context_Environment $environment, $context_class): array
    {
        $callees = [];
        foreach ($this->context_readers as $loader) {
            $callees = array_merge($callees, $loader->read_context_callees($environment, $context_class));
        }

        return $callees;
    }
}
