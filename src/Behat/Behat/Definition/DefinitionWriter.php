<?php

declare(strict_types=1);

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

use Behat\Behat\Definition\Printer\DefinitionPrinter;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Suite\Suite;

/**
 * Prints definitions using provided printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class DefinitionWriter
{
    /**
     * Initializes writer.
     */
    public function __construct(
        private EnvironmentManager $environmentManager,
        private DefinitionRepository $repository,
    ) {
    }

    /**
     * Prints definitions for provided suite using printer.
     */
    public function printSuiteDefinitions(DefinitionPrinter $printer, \Behat\Testwork\Suite\Suite $suite): void
    {
        $environment = $this->environmentManager->buildEnvironment($suite);
        $definitions = $this->repository->getEnvironmentDefinitions($environment);

        $printer->printDefinitions($suite, $definitions);
    }
}
