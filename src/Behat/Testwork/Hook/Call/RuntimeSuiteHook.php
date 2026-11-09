<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Call\Exception\Bad_Callback_Exception;
use Behat\Testwork\Hook\Scope\Hook_Scope;
use Behat\Testwork\Hook\Scope\Suite_Scope;
use Behat\Testwork\Suite\Suite;
/**
 * Represents suite hook executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Suite_Hook extends Runtime_Filterable_Hook
{
    /**
     * Initializes hook.
     *
     * @param string                               $scopeName
     * @param string|null                          $filterString
     * @param callable|array{class-string, string} $callable
     * @param string|null                          $description
     *
     * @throws BadCallbackException If callback is method, but not a static one
     */
    public function __construct($scope_name, $filter_string, $callable, $description = null)
    {
        parent::__construct($scope_name, $filter_string, $callable, $description);
        $this->throw_if_instance_method($callable, 'Suite');
    }
    public function filter_matches(Hook_Scope $scope)
    {
        if (!$scope instanceof Suite_Scope) {
            return false;
        }
        if (null === $filter_string = $this->get_filter_string()) {
            return true;
        }
        if (!empty($filter_string)) {
            return $this->is_suite_match($scope->get_suite(), $filter_string);
        }
        return false;
    }
    /**
     * Checks if Feature matches specified filter.
     *
     * @param string $filterString
     */
    private function is_suite_match(Suite $suite, $filter_string): bool
    {
        if ('/' === $filter_string[0]) {
            return 1 === preg_match($filter_string, $suite->get_name());
        }
        return false !== mb_strpos($suite->get_name(), $filter_string, 0, 'utf8');
    }
}