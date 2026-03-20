<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\Handler\Context_Environment_Handler;
use Behat\Behat\Context\Exception\Context_Not_Found_Exception;
use Behat\Behat\Helper_Container\Environment\Service_Container_Environment;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;
use Psr\Container\Container_Interface;
/**
 * Context environment based on a list of instantiated context objects.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Initialized_Context_Environment implements Context_Environment, Service_Container_Environment
{
    private ?Container_Interface $service_container = null;
    /**
     * @var array<class-string<Context>, Context>
     *
     * TODO use a class-string-map type to have an accurate type once https://github.com/phpstan/phpstan/issues/9521 is implemented
     */
    private array $contexts = [];
    /**
     * Initializes environment.
     */
    public function __construct(private readonly Suite $suite)
    {
    }
    /**
     * Registers context instance in the environment.
     */
    public function register_context(Context $context): void
    {
        $this->contexts[$context::class] = $context;
    }
    public function set_service_container(?Container_Interface $container = null): void
    {
        $this->service_container = $container;
    }
    public function get_suite(): Suite
    {
        return $this->suite;
    }
    public function has_contexts(): bool
    {
        return count($this->contexts) > 0;
    }
    public function get_context_classes(): array
    {
        return array_keys($this->contexts);
    }
    public function has_context_class($class): bool
    {
        return isset($this->contexts[$class]);
    }
    /**
     * Returns list of registered context instances.
     *
     * @return list<Context>
     */
    public function get_contexts(): array
    {
        return array_values($this->contexts);
    }
    /**
     * Returns registered context by its class name.
     *
     * @template T of Context
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws ContextNotFoundException If context is not in the environment
     */
    public function get_context($class)
    {
        if (!$this->has_context_class($class)) {
            throw new Context_Not_Found_Exception(sprintf('`%s` context is not found in the suite environment. Have you registered it?', $class), $class);
        }
        return $this->contexts[$class];
    }
    public function get_service_container(): ?Container_Interface
    {
        return $this->service_container;
    }
    public function bind_callee(Callee $callee)
    {
        $callable = $callee->get_callable();
        if ($callee->is_an_instance_method() && is_array($callable)) {
            return [$this->get_context($callable[0]), $callable[1]];
        }
        return $callable;
    }
}