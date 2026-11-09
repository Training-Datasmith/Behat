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

use Behat\Behat\Definition\Exception\Redundant_Step_Exception;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Environment_Manager;

/**
 * Provides step definitions using environment manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Definition_Repository
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
     * @return list<Definition>
     *
     * @throws Redundant_Step_Exception
     */
    public function get_environment_definitions(Environment $environment): array
    {
        $patterns = [];
        $definitions = [];
        foreach ($this->environment_manager->read_environment_callees($environment) as $callee) {
            if (!$callee instanceof Definition) {
                continue;
            }
            $pattern = $callee->get_pattern();
            if (isset($patterns[$pattern])) {
                throw new Redundant_Step_Exception($callee, $patterns[$pattern]);
            }
            $patterns[$pattern] = $callee;
            $definitions[] = $callee;
        }

        return $definitions;
    }
}
