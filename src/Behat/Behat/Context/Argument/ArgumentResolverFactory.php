<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Argument;

use Behat\Testwork\Environment\Environment;
/**
 * Creates argument resolvers for provided environment.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Argument_Resolver_Factory
{
    /**
     * Builds argument resolvers for provided suite.
     *
     * @return ArgumentResolver[]
     */
    public function create_argument_resolvers(Environment $environment);
}