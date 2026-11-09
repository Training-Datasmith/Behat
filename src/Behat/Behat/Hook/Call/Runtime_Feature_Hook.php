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

use Behat\Behat\Hook\Scope\Feature_Scope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Hook\Call\Runtime_Filterable_Hook;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Represents a feature hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Feature_Hook extends Runtime_Filterable_Hook
{
    /**
     * Initializes hook.
     *
     * @param callable|array{class-string, string} $callable
     * @param string|null                          $description
     *
     * @throws Bad_Callback_Exception If callback is method, but not a static one
     */
    public function __construct($scope_name, $filter_string, $callable, $description = null)
    {
        parent::__construct($scope_name, $filter_string, $callable, $description);
        $this->throw_if_instance_method($callable, 'Feature');
    }

    public function filter_matches(Hook_Scope $scope)
    {
        if (!$scope instanceof Feature_Scope) {
            return false;
        }
        if (null === $filter_string = $this->get_filter_string()) {
            return true;
        }

        return $this->is_match($scope->get_feature(), $filter_string);
    }

    /**
     * @return bool
     */
    private function is_match(FeatureNode $feature, $filter_string)
    {
        if (str_contains($filter_string, '@')) {
            return $this->is_match_tag_filter($feature, $filter_string);
        }
        if (!empty($filter_string)) {
            return $this->is_match_name_filter($feature, $filter_string);
        }

        return false;
    }

    /**
     * Checks if feature matches tag filter.
     *
     * @return bool
     */
    private function is_match_tag_filter(FeatureNode $feature, string $filter_string)
    {
        $filter = new TagFilter($filter_string);

        return $filter->isFeatureMatch($feature);
    }

    /**
     * Checks if feature matches name filter.
     *
     * @return bool
     */
    private function is_match_name_filter(FeatureNode $feature, $filter_string)
    {
        $filter = new NameFilter($filter_string);

        return $filter->isFeatureMatch($feature);
    }
}
