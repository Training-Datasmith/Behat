<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container\Argument;

use Behat\Behat\Context\Argument\Argument_Resolver;
use Behat\Behat\Helper_Container\Argument_Autowirer;
use Psr\Container\Container_Interface;
use ReflectionClass;
/**
 * Resolves arguments that weren't resolved before by autowiring.
 *
 * @see ContextFactory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Autowiring_Resolver implements Argument_Resolver
{
    private Argument_Autowirer $autowirer;
    /**
     * Initialises resolver.
     */
    public function __construct(Container_Interface $container)
    {
        $this->autowirer = new Argument_Autowirer($container);
    }
    public function resolve_arguments(ReflectionClass $class_reflection, array $arguments)
    {
        if ($constructor = $class_reflection->get_constructor()) {
            return $this->autowirer->autowire_arguments($constructor, $arguments);
        }
        return $arguments;
    }
}