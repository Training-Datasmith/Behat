<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Environment_Manager;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Finds hooks using provided environments or scopes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hook_Repository
{
    /**
     * Initializes repository.
     */
    public function __construct(private Environment_Manager $environment_manager)
    {
    }

    /**
     * Returns all available hooks for a specific environment.
     *
     * @return Hook[]
     */
    public function get_environment_hooks(Environment $environment): array
    {
        return array_filter($this->environment_manager->read_environment_callees($environment), fn (Callee $callee): bool => $callee instanceof Hook);
    }

    /**
     * Returns hooks for a specific event.
     *
     * @return Hook[]
     */
    public function get_scope_hooks(Hook_Scope $scope): array
    {
        return array_filter($this->get_environment_hooks($scope->get_environment()), function (Hook $hook) use ($scope): bool {
            if ($scope->get_name() !== $hook->get_scope_name()) {
                return false;
            }

            return !($hook instanceof Filterable_Hook && !$hook->filter_matches($scope));
        });
    }
}
