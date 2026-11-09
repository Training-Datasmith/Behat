<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Environment\Call\Environment_Call;
use Behat\Testwork\Hook\Hook;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Represents a hook call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Hook_Call extends Environment_Call
{
    private readonly Hook_Scope $scope;

    /**
     * Initializes hook call.
     */
    public function __construct(Hook_Scope $scope, Hook $hook, $error_reporting_level = null)
    {
        parent::__construct($scope->get_environment(), $hook, [$scope], $error_reporting_level);
        $this->scope = $scope;
    }

    /**
     * Returns hook scope.
     */
    public function get_scope(): Hook_Scope
    {
        return $this->scope;
    }
}
