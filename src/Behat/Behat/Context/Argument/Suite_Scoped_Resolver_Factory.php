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

use Behat\Testwork\Suite\Suite;
/**
 * Creates argument resolvers for provided suite.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated since 3.4. Use `ArgumentResolverFactory` instead
 */
interface Suite_Scoped_Resolver_Factory
{
    /**
     * Creates argument resolvers for provided suite.
     *
     * @return ArgumentResolver[]
     */
    public function generate_argument_resolvers(Suite $suite);
}