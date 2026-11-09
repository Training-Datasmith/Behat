<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\Unknown_Parameter_Value_Exception;
use Behat\Testwork\Argument\Exception\Unsupported_Function_Exception;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Organises constructor arguments.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Constructor_Argument_Organiser implements Argument_Organiser
{
    /**
     * Initializes organiser.
     */
    public function __construct(private Argument_Organiser $base_organiser)
    {
    }

    public function organise_arguments(ReflectionFunctionAbstract $function, array $arguments)
    {
        if (!$function instanceof ReflectionMethod) {
            throw new Unsupported_Function_Exception(sprintf('Constructor_Argument_Organiser can only work with ReflectionMethod, but `%s` given.', $function::class));
        }
        $organised_arguments = $this->base_organiser->organise_arguments($function, $arguments);
        $this->validate_arguments($function, $arguments, $organised_arguments);

        return $organised_arguments;
    }

    /**
     * Checks that all provided constructor arguments are present in the constructor.
     *
     * @throws Unknown_Parameter_Value_Exception
     */
    private function validate_arguments(ReflectionMethod $constructor, array $passed_arguments, array $organised_arguments): void
    {
        foreach (array_keys($passed_arguments) as $key) {
            if (array_key_exists($key, $organised_arguments)) {
                continue;
            }
            throw new Unknown_Parameter_Value_Exception(sprintf('`%s::__construct()` does not expect argument `$%s`.', $constructor->getDeclaringClass()->getName(), $key));
        }
    }
}
