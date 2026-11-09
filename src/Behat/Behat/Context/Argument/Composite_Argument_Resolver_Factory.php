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

use Behat\Testwork\Environment\Environment;

/**
 * Composite factory. Delegates to other (registered) factories to do the job.
 *
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Composite_Argument_Resolver_Factory implements Argument_Resolver_Factory
{
    /**
     * @var Argument_Resolver_Factory[]
     */
    private array $factories = [];

    /**
     * Registers factory.
     */
    public function register_factory(Argument_Resolver_Factory $factory): void
    {
        $this->factories[] = $factory;
    }

    public function create_argument_resolvers(Environment $environment): array
    {
        return array_reduce($this->factories, fn (array $resolvers, Argument_Resolver_Factory $factory): array => array_merge($resolvers, $factory->create_argument_resolvers($environment)), []);
    }
}
