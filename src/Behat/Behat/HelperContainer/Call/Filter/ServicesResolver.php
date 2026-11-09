<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container\Call\Filter;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Helper_Container\Argument_Autowirer;
use Behat\Behat\Helper_Container\Environment\Service_Container_Environment;
use Behat\Behat\Helper_Container\Exception\Unsupported_Call_Exception;
use Behat\Behat\Transformation\Call\Transformation_Call;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\Call_Filter;
use Behat\Testwork\Environment\Call\Environment_Call;
use Psr\Container\Container_Exception_Interface;
use Psr\Container\Container_Interface;
/**
 * Dynamically resolves call arguments using the service container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Services_Resolver implements Call_Filter
{
    public function supports_call(Call $call): bool
    {
        return ($call instanceof Definition_Call || $call instanceof Transformation_Call) && $call->get_environment() instanceof Service_Container_Environment;
    }
    /**
     * Filters a call and returns a new one.
     *
     * @return Call
     *
     * @throws UnsupportedCallException
     * @throws ContainerExceptionInterface
     */
    public function filter_call(Call $call)
    {
        if ($container = $this->get_container($call)) {
            $autowirer = new Argument_Autowirer($container);
            $new_arguments = $autowirer->autowire_arguments($call->get_callee()->get_reflection(), $call->get_arguments());
            return $this->repackage_call_if_new_arguments($call, $new_arguments);
        }
        return $call;
    }
    /**
     * Gets container from the call.
     *
     * @return ContainerInterface|null
     *
     * @throws UnsupportedCallException if given call is not EnvironmentCall or environment is not ServiceContainerEnvironment
     */
    private function get_container(Call $call)
    {
        if (!$call instanceof Environment_Call) {
            throw new Unsupported_Call_Exception(sprintf('ServicesResolver can not filter `%s` call.', $call::class), $call);
        }
        $environment = $call->get_environment();
        if (!$environment instanceof Service_Container_Environment) {
            throw new Unsupported_Call_Exception(sprintf('ServicesResolver can not filter `%s` call.', $call::class), $call);
        }
        return $environment->get_service_container();
    }
    /**
     * Repackages old calls with new arguments, but only if two differ.
     *
     *
     * @throws UnsupportedCallException if given call is not DefinitionCall or TransformationCall
     */
    private function repackage_call_if_new_arguments(Call $call, array $arguments): \Behat\Testwork\Call\Call|\Behat\Behat\Definition\Call\Definition_Call|\Behat\Behat\Transformation\Call\Transformation_Call
    {
        if ($arguments === $call->get_arguments()) {
            return $call;
        }
        return $this->repackage_call_with_new_arguments($call, $arguments);
    }
    /**
     * Repackages old calls with new arguments.
     *
     * @throws UnsupportedCallException
     */
    private function repackage_call_with_new_arguments(Call $call, array $new_arguments): Definition_Call|Transformation_Call
    {
        if ($call instanceof Definition_Call) {
            return $this->repackage_definition_call($call, $new_arguments);
        }
        if ($call instanceof Transformation_Call) {
            return $this->repackage_transformation_call($call, $new_arguments);
        }
        throw new Unsupported_Call_Exception(sprintf('ServicesResolver can not filter `%s` call.', $call::class), $call);
    }
    /**
     * Repackages definition call with new arguments.
     *
     * @throws UnsupportedCallException
     */
    private function repackage_definition_call(Definition_Call $call, array $new_arguments): Definition_Call
    {
        $definition = $call->get_callee();
        return new Definition_Call($call->get_environment(), $call->get_feature(), $call->get_step(), $definition, $new_arguments, $call->get_error_reporting_level());
    }
    /**
     * Repackages transformation call with new arguments.
     *
     * @throws UnsupportedCallException
     */
    private function repackage_transformation_call(Transformation_Call $call, array $new_arguments): Transformation_Call
    {
        $transformation = $call->get_callee();
        if (!$transformation instanceof Transformation) {
            throw new Unsupported_Call_Exception(sprintf('Something is wrong in callee associated with `%s` call.', $call::class), $call);
        }
        return new Transformation_Call($call->get_environment(), $call->get_definition(), $transformation, $new_arguments);
    }
}