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

use Behat\Testwork\Call\Runtime_Callee;
use Behat\Testwork\Hook\Hook;
use Stringable;

/**
 * Represents a hook executed during the execution runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Hook extends Runtime_Callee implements Stringable, Hook
{
    /**
     * Initializes hook.
     *
     * @param callable    $callable
     */
    public function __construct(private $scope_name, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }

    public function get_scope_name()
    {
        return $this->scope_name;
    }

    public function __toString(): string
    {
        return $this->get_name();
    }
}
