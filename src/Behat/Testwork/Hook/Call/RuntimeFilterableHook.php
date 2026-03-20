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

use Behat\Testwork\Hook\Filterable_Hook;
use Stringable;
/**
 * Represents runtime hook, filterable by filter string.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Filterable_Hook extends Runtime_Hook implements Stringable, Filterable_Hook
{
    /**
     * Initializes hook.
     *
     * @param string      $scopeName
     * @param string|null $filterString
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($scope_name, private $filter_string, $callable, $description = null)
    {
        parent::__construct($scope_name, $callable, $description);
    }
    /**
     * Returns hook filter string (if has one).
     *
     * @return string|null
     */
    public function get_filter_string()
    {
        return $this->filter_string;
    }
    public function __toString(): string
    {
        return trim($this->get_name() . ' ' . $this->get_filter_string());
    }
}