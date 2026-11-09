<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Environment_Manager;

/**
 * Provides transformations using environment manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Transformation_Repository
{
    /**
     * Initializes repository.
     */
    public function __construct(private Environment_Manager $environment_manager)
    {
    }

    /**
     * Returns all available definitions for a specific environment.
     *
     * @return Transformation[]
     */
    public function get_environment_transformations(Environment $environment): array
    {
        return array_filter($this->environment_manager->read_environment_callees($environment), fn (Callee $callee): bool => $callee instanceof Transformation);
    }
}
