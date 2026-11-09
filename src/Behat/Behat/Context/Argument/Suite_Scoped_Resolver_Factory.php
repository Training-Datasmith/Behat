<?php

declare(strict_types=1);
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
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated since 3.4. Use `Argument_Resolver_Factory` instead
 */
interface Suite_Scoped_Resolver_Factory
{
    /**
     * Creates argument resolvers for provided suite.
     *
     * @return Argument_Resolver[]
     */
    public function generate_argument_resolvers(Suite $suite);
}
