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

use Behat\Testwork\Hook\Scope\Suite_Scope;
/**
 * Represents BeforeSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Suite extends Runtime_Suite_Hook
{
    /**
     * Initializes hook.
     *
     * @param string|null $filterString
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($filter_string, $callable, $description = null)
    {
        parent::__construct(Suite_Scope::BEFORE, $filter_string, $callable, $description);
    }
    /**
     * Returns hook name.
     */
    public function get_name(): string
    {
        return 'BeforeSuite';
    }
}