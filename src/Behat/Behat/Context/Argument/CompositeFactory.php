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
 * Composite factory. Delegates to other (registered) factories to do the job.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated and will be removed in 4.0. Use CompositeArgumentResolverFactory instead
 */
final class Composite_Factory implements Suite_Scoped_Resolver_Factory
{
    /**
     * @var SuiteScopedResolverFactory[]
     */
    private array $factories = [];
    /**
     * Registers factory.
     */
    public function register_factory(Suite_Scoped_Resolver_Factory $factory): void
    {
        $this->factories[] = $factory;
    }
    public function generate_argument_resolvers(Suite $suite): array
    {
        return array_reduce($this->factories, fn(array $resolvers, Suite_Scoped_Resolver_Factory $factory): array => array_merge($resolvers, $factory->generate_argument_resolvers($suite)), []);
    }
}