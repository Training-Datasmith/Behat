<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container\Environment;

use Behat\Testwork\Environment\Environment;
use Psr\Container\Container_Interface;
/**
 * Represents test environment based on a service locator pattern.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Service_Container_Environment extends Environment
{
    /**
     * Sets/unsets service container for the environment.
     */
    public function set_service_container(?Container_Interface $container = null);
    /**
     * Returns environment service container if set.
     *
     * @return ContainerInterface|null
     */
    public function get_service_container();
}