<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\Scenario_Scope;
use Behat\Gherkin\Filter\Name_Filter;
use Behat\Gherkin\Filter\Tag_Filter;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface;
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
     * @param string            $filterString
     *
     * @return bool
     */
    protected function is_match(Feature_Node $feature, Scenario_Interface $scenario, $filter_string)
    {
        if (str_contains($filter_string, '@')) {
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
     * @param string            $filterString
     *
     * @return bool
     */
    protected function is_match_tag_filter(Feature_Node $feature, Scenario_Interface $scenario, $filter_string)
    {
        $filter = new Tag_Filter($filter_string);
        return $filter->is_scenario_match($feature, $scenario);
    }
    /**
     * Checks if scenario matches name filter.
     *
     * @param string            $filterString
     *
     * @return bool
     */
    protected function is_match_name_filter(Scenario_Interface $scenario, $filter_string)
    {
        $filter = new Name_Filter($filter_string);
        return $filter->is_scenario_match($scenario);
    }
}