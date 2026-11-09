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
use Behat\Testwork\Suite\Suite;
/**
 * NoOp factory. Always returns zero resolvers.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Null_Factory implements Argument_Resolver_Factory, Suite_Scoped_Resolver_Factory
{
    public function generate_argument_resolvers(Suite $suite): array
    {
        return [];
    }
    public function create_argument_resolvers(Environment $environment): array
    {
        return [];
    }
}