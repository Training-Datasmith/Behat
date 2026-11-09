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

use Behat\Behat\Hook\Scope\Scenario_Scope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Hook\Call\Runtime_Filterable_Hook;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Represents a scenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Scenario_Hook extends Runtime_Filterable_Hook
{
    public function filter_matches(Hook_Scope $scope)
    {
        if (!$scope instanceof Scenario_Scope) {
            return false;
        }
        if (null === $filter_string = $this->get_filter_string()) {
            return true;
        }

        return $this->is_match($scope->get_feature(), $scope->get_scenario(), $filter_string);
    }

    /**
     * Checks if nodes match filter.
     *
     * @return bool
     */
    protected function is_match(FeatureNode $feature, ScenarioLikeInterface $scenario, $filter_string)
    {
        if (str_contains((string) $filter_string, '@')) {
            return $this->is_match_tag_filter($feature, $scenario, $filter_string);
        }
        if (!empty($filter_string)) {
            return $this->is_match_name_filter($scenario, $filter_string);
        }

        return false;
    }

    /**
     * Checks if node match tag filter.
     *
     * @return bool
     */
    protected function is_match_tag_filter(FeatureNode $feature, ScenarioLikeInterface $scenario, $filter_string)
    {
        $filter = new TagFilter($filter_string);

        return $filter->isScenarioMatch($feature, $scenario);
    }

    /**
     * Checks if scenario matches name filter.
     *
     * @return bool
     */
    protected function is_match_name_filter(ScenarioLikeInterface $scenario, $filter_string)
    {
        $filter = new NameFilter($filter_string);

        return $filter->isScenarioMatch($scenario);
    }
}
