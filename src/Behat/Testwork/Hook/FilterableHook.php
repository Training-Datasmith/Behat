<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Hook;

use Behat\Testwork\Hook\Scope\Hook_Scope;
/**
 * Represents hook that is filterable by the provided scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Filterable_Hook extends Hook
{
    /**
     * Checks that current hook matches provided hook scope.
     *
     * @return bool
     */
    public function filter_matches(Hook_Scope $scope);
}