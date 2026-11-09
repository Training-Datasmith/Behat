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
 * Adapts Suite_Scoped_Resolver_Factory to new Argument_Resolver_Factory interface.
 *
 * @see Context_Environment_Handler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated since 3.4. Use `Argument_Resolver_Factory` instead
 */
final readonly class Suite_Scoped_Resolver_Factory_Adapter implements Argument_Resolver_Factory
{
    /**
     * Initialises adapter.
     */
    public function __construct(private Suite_Scoped_Resolver_Factory $factory)
    {
    }

    public function create_argument_resolvers(Environment $environment)
    {
        return $this->factory->generate_argument_resolvers($environment->get_suite());
    }
}
