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

use Behat\Testwork\Call\Exception\Call_Handling_Exception;
use Behat\Testwork\Call\Exception\Fatal_Throwable_Error;
use Behat\Testwork\Call\Filter\Call_Filter;
use Behat\Testwork\Call\Filter\Result_Filter;
use Behat\Testwork\Call\Handler\Call_Handler;
use Behat\Testwork\Call\Handler\Exception_Handler;
use Exception;
use Throwable;
/**
 * Makes calls and handles results using registered handlers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Call_Center
{
    /**
     * @var CallFilter[]
     */
    private array $call_filters = [];
    /**
     * @var CallHandler[]
     */
    private array $call_handlers = [];
    /**
     * @var ResultFilter[]
     */
    private array $result_filters = [];
    /**
     * @var ExceptionHandler[]
     */
    private array $exception_handlers = [];
    /**
     * Registers call filter.
     */
    public function register_call_filter(Call_Filter $filter): void
    {
        $this->call_filters[] = $filter;
    }
    /**
     * Registers call handler.
     */
    public function register_call_handler(Call_Handler $handler): void
    {
        $this->call_handlers[] = $handler;
    }
    /**
     * Registers call result filter.
     */
    public function register_result_filter(Result_Filter $filter): void
    {
        $this->result_filters[] = $filter;
    }
    /**
     * Registers result exception handler.
     */
    public function register_exception_handler(Exception_Handler $handler): void
    {
        $this->exception_handlers[] = $handler;
    }
    /**
     * Handles call and its result using registered filters and handlers.
     *
     * @return CallResult
     */
    public function make_call(Call $call)
    {
        try {
            return $this->filter_result($this->handle_call($this->filter_call($call)));
        } catch (Throwable $exception) {
            return new Call_Result($call, null, $this->handle_exception($exception));
        }
    }
    /**
     * Filters call using registered filters and returns a filtered one.
     *
     * @return Call
     */
    private function filter_call(Call $call)
    {
        foreach ($this->call_filters as $filter) {
            if (!$filter->supports_call($call)) {
                continue;
            }
            $call = $filter->filter_call($call);
        }
        return $call;
    }
    /**
     * Handles call using registered call handlers.
     *
     * @return CallResult
     *
     * @throws CallHandlingException If call handlers didn't produce call result
     */
    private function handle_call(Call $call)
    {
        foreach ($this->call_handlers as $handler) {
            if (!$handler->supports_call($call)) {
                continue;
            }
            return $handler->handle_call($call);
        }
        throw new Call_Handling_Exception(sprintf('None of the registered call handlers could handle a `%s` call.', $call->get_callee()->get_path()), $call);
    }
    /**
     * Filters call result using registered filters and returns a filtered one.
     *
     * @return CallResult
     */
    private function filter_result(Call_Result $result)
    {
        foreach ($this->result_filters as $filter) {
            if (!$filter->supports_result($result)) {
                continue;
            }
            $result = $filter->filter_result($result);
        }
        return $result;
    }
    /**
     * Handles exception using registered handlers and returns a handled one.
     *
     *
     */
    private function handle_exception(\Throwable $exception): \Behat\Testwork\Call\Exception\Fatal_Throwable_Error|\Exception
    {
        foreach ($this->exception_handlers as $handler) {
            if (!$handler->supports_exception($exception)) {
                continue;
            }
            $exception = $handler->handle_exception($exception);
        }
        if (!$exception instanceof Exception) {
            return new Fatal_Throwable_Error($exception);
        }
        return $exception;
    }
}