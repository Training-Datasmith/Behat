<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\Step_Scope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Hook\Call\Runtime_Filterable_Hook;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Represents a step hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Step_Hook extends Runtime_Filterable_Hook
{
    public function filter_matches(Hook_Scope $scope)
    {
        if (!$scope instanceof Step_Scope) {
            return false;
        }
        if (null === $filter_string = $this->get_filter_string()) {
            return true;
        }
        if (!empty($filter_string)) {
            $filter = new NameFilter($filter_string);
            if ($filter->isFeatureMatch($scope->get_feature())) {
                return true;
            }

            return $this->is_step_match($scope->get_step(), $filter_string);
        }

        return false;
    }

    /**
     * Checks if Feature matches specified filter.
     */
    private function is_step_match(StepNode $step, $filter_string): bool
    {
        if ('/' === $filter_string[0]) {
            return 1 === preg_match($filter_string, $step->getText());
        }

        return false !== mb_strpos($step->getText(), (string) $filter_string, 0, 'utf8');
    }
}
