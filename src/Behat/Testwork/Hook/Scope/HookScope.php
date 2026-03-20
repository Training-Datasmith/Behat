<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Hook_Repository;
use Behat\Testwork\Suite\Suite;
/**
 * Represents an object used to find appropriate hooks.
 *
 * @see HookDispatcher
 * @see HookRepository
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Hook_Scope
{
    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function get_name();
    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function get_suite();
    /**
     * Returns hook environment.
     *
     * @return Environment
     */
    public function get_environment();
}