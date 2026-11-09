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

use Behat\Behat\Definition\Printer\Definition_Printer;
use Behat\Testwork\Environment\Environment_Manager;
use Behat\Testwork\Suite\Suite;

/**
 * Prints definitions using provided printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Definition_Writer
{
    /**
     * Initializes writer.
     */
    public function __construct(private Environment_Manager $environment_manager, private Definition_Repository $repository)
    {
    }

    /**
     * Prints definitions for provided suite using printer.
     */
    public function print_suite_definitions(Definition_Printer $printer, Suite $suite): void
    {
        $environment = $this->environment_manager->build_environment($suite);
        $definitions = $this->repository->get_environment_definitions($environment);
        $printer->print_definitions($suite, $definitions);
    }
}
