<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Environment\Reader;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Environment_Manager;
/**
 * Reads callees from a provided environment.
 *
 * @see EnvironmentManager
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Environment_Reader
{
    /**
     * Checks if reader supports an environment.
     *
     * @return bool
     */
    public function supports_environment(Environment $environment);
    /**
     * Reads callees from an environment.
     *
     * @return Callee[]
     */
    public function read_environment_callees(Environment $environment);
}