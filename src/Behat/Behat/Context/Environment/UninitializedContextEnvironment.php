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
use Behat\Behat\Context\Exception\Wrong_Context_Class_Exception;
use Behat\Testwork\Environment\Static_Environment;
use ReflectionClass;
/**
 * Context environment based on a list of context classes.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Uninitialized_Context_Environment extends Static_Environment implements Context_Environment
{
    /**
     * @var array<class-string<Context>, array>
     */
    private array $context_classes = [];
    /**
     * Registers context class.
     *
     * @param class-string<Context> $contextClass
     *
     * @throws ContextNotFoundException   If class does not exist
     * @throws WrongContextClassException if class does not implement Context interface
     */
    public function register_context_class($context_class, ?array $arguments = null): void
    {
        if (!class_exists($context_class)) {
            throw new Context_Not_Found_Exception(sprintf('`%s` context class not found and can not be used.', $context_class), $context_class);
        }
        $refl_class = new ReflectionClass($context_class);
        if (!$refl_class->implements_interface(Context::class)) {
            throw new Wrong_Context_Class_Exception(sprintf('Every context class must implement Behat Context interface, but `%s` does not.', $context_class), $context_class);
        }
        $this->context_classes[$context_class] = $arguments ?: [];
    }
    public function has_contexts(): bool
    {
        return count($this->context_classes) > 0;
    }
    public function get_context_classes(): array
    {
        return array_keys($this->context_classes);
    }
    public function has_context_class($class): bool
    {
        return isset($this->context_classes[$class]);
    }
    /**
     * Returns context classes with their arguments.
     *
     * @return array<class-string<Context>, array>
     */
    public function get_context_classes_with_arguments()
    {
        return $this->context_classes;
    }
}