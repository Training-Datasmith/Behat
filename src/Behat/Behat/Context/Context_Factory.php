<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context;

use Behat\Behat\Context\Argument\Argument_Resolver;
use Behat\Behat\Context\Initializer\Context_Initializer;
use Behat\Testwork\Argument\Argument_Organiser;
use Behat\Testwork\Argument\Validator;
use ReflectionClass;

/**
 * Instantiates contexts using registered argument resolvers and context initializers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Factory
{
    /**
     * @var Argument_Resolver[]
     */
    private array $argument_resolvers = [];
    /**
     * @var Context_Initializer[]
     */
    private array $context_initializers = [];
    private readonly Validator $validator;

    /**
     * Initialises factory.
     */
    public function __construct(private readonly Argument_Organiser $argument_organiser)
    {
        $this->validator = new Validator();
    }

    /**
     * Registers context argument resolver.
     */
    public function register_argument_resolver(Argument_Resolver $resolver): void
    {
        $this->argument_resolvers[] = $resolver;
    }

    /**
     * Registers context initializer.
     */
    public function register_context_initializer(Context_Initializer $initializer): void
    {
        $this->context_initializers[] = $initializer;
    }

    /**
     * Creates and initializes context class.
     *
     * @param class-string<Context> $class
     *
     * @return Context
     */
    public function create_context($class, array $arguments = [], array $single_use_resolvers = [])
    {
        $reflection = new ReflectionClass($class);
        $resolvers = array_merge($single_use_resolvers, $this->argument_resolvers);
        $resolved_arguments = $this->resolve_arguments($reflection, $arguments, $resolvers);
        $context = $this->create_instance($reflection, $resolved_arguments);
        $this->initialize_instance($context);

        return $context;
    }

    /**
     * Resolves arguments for a specific class using registered argument resolvers.
     *
     * @param Argument_Resolver[] $resolvers
     *
     * @return mixed[]
     */
    private function resolve_arguments(ReflectionClass $reflection, array $arguments, array $resolvers)
    {
        $new_arguments = $arguments;
        foreach ($resolvers as $resolver) {
            $new_arguments = $resolver->resolve_arguments($reflection, $new_arguments);
        }
        if (!$reflection->hasMethod('__construct')) {
            return $new_arguments;
        }
        $constructor = $reflection->getConstructor();
        $new_arguments = $this->argument_organiser->organise_arguments($constructor, $new_arguments);
        $this->validator->validate_arguments($constructor, $new_arguments);

        return $new_arguments;
    }

    /**
     * Creates context instance.
     */
    private function create_instance(ReflectionClass $reflection, array $arguments): Context
    {
        if (count($arguments)) {
            return $reflection->newInstanceArgs(array_values($arguments));
        }

        return $reflection->newInstance();
    }

    /**
     * Initializes context class and returns new context instance.
     */
    private function initialize_instance(Context $context): void
    {
        foreach ($this->context_initializers as $initializer) {
            $initializer->initialize_context($context);
        }
    }
}
