<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Helper_Container\Argument;

use Behat\Behat\Context\Argument\Argument_Resolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Resolves arguments using provided service container.
 *
 * @see Context_Factory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Services_Resolver implements Argument_Resolver
{
    /**
     * Initialises resolver.
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function resolve_arguments(ReflectionClass $class_reflection, array $arguments): array
    {
        return array_map($this->resolve_argument(...), $arguments);
    }

    /**
     * Attempts to resolve singular argument from container.
     *
     * Convention is strings starting with `@` are considered services and
     * are expected to be present in the container.
     *
     * @throws ContainerExceptionInterface
     */
    private function resolve_argument($value)
    {
        if (is_string($value) && 0 === mb_strpos($value, '@')) {
            return $this->container->get(mb_substr($value, 1));
        }

        return $value;
    }
}
