<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Call;

use Behat\Testwork\Call\Exception\Bad_Callback_Exception;
use ReflectionFunction;
use Reflection_Function_Abstract;
use ReflectionMethod;
/**
 * Represents callee created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Runtime_Callee implements Callee
{
    /**
     * @var callable|array{class-string, string}
     */
    private $callable;
    private ReflectionMethod|ReflectionFunction $reflection;
    private string $path;
    /**
     * Initializes callee.
     */
    public function __construct(callable|array $callable, private readonly ?string $description = null)
    {
        if (is_array($callable)) {
            $this->reflection = new ReflectionMethod($callable[0], $callable[1]);
            $this->path = $callable[0] . '::' . $callable[1] . '()';
        } else {
            $this->reflection = new ReflectionFunction($callable);
            $this->path = $this->reflection->get_file_name() . ':' . $this->reflection->get_start_line();
        }
        $this->callable = $callable;
    }
    /**
     * Returns callee description.
     *
     * @return string
     */
    public function get_description(): ?string
    {
        return $this->description;
    }
    /**
     * Returns callee definition path.
     */
    public function get_path(): string
    {
        return $this->path;
    }
    /**
     * Returns callable.
     *
     * @return callable|array{class-string, string}
     */
    public function get_callable()
    {
        return $this->callable;
    }
    /**
     * Returns callable reflection.
     *
     * @return ReflectionFunctionAbstract
     */
    public function get_reflection(): \ReflectionMethod|\ReflectionFunction
    {
        return $this->reflection;
    }
    /**
     * Returns true if callee is a method, false otherwise.
     */
    public function is_a_method(): bool
    {
        return $this->reflection instanceof ReflectionMethod;
    }
    /**
     * Returns true if callee is an instance (non-static) method, false otherwise.
     */
    public function is_an_instance_method(): bool
    {
        return $this->reflection instanceof ReflectionMethod && !$this->reflection->is_static();
    }
    /**
     * @param callable|array{class-string, string} $callable
     */
    protected function throw_if_instance_method(callable|array $callable, string $hook_type): void
    {
        if ($this->is_an_instance_method()) {
            if (is_array($callable)) {
                $class_name = $callable[0];
                $method_name = $callable[1];
            } else {
                $reflection = new ReflectionMethod($callable);
                $class_name = $reflection->get_declaring_class()->get_short_name();
                $method_name = $reflection->get_name();
            }
            throw new Bad_Callback_Exception(sprintf('%s hook callback: %s::%s() must be a static method', $hook_type, $class_name, $method_name), $callable);
        }
    }
}