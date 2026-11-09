<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Behat\Testwork\Argument\Validator;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Call_Result;
use Behat\Testwork\Call\Exception\Call_Error_Exception;
use Exception;

/**
 * Handles calls in the current runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Runtime_Call_Handler implements Call_Handler
{
    private bool $ob_started = false;
    private readonly Validator $validator;
    private ?int $previous_error_reporting = null;

    /**
     * Initializes executor.
     */
    public function __construct(private $error_reporting_level = E_ALL)
    {
        $this->validator = new Validator();
    }

    public function supports_call(Call $call): bool
    {
        return true;
    }

    public function handle_call(Call $call)
    {
        $this->start_error_and_output_buffering($call);
        $result = $this->execute_call($call);
        $this->stop_error_and_output_buffering();

        return $result;
    }

    /**
     * Used as a custom error handler when step is running.
     *
     * @see set_error_handler()
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws Call_Error_Exception
     */
    public function handle_error($level, $message, $file, $line): bool
    {
        if ($this->error_level_is_not_reportable($level)) {
            return false;
        }
        throw new Call_Error_Exception($level, $message, $file, $line);
    }

    /**
     * Executes single call.
     */
    private function execute_call(Call $call): Call_Result
    {
        $reflection = $call->get_callee()->get_reflection();
        $callable = $call->get_bound_callable();
        $arguments = $call->get_arguments();
        $return = $exception = null;
        try {
            $arguments = array_values($arguments);
            $this->validator->validate_arguments($reflection, $arguments);
            $return = $callable(...$arguments);
        } catch (Exception $caught) {
            $exception = $caught;
        }
        $std_out = $this->get_buffered_std_out();

        return new Call_Result($call, $return, $exception, $std_out);
    }

    /**
     * Returns buffered stdout.
     *
     * @return string|null
     */
    private function get_buffered_std_out(): string|false|null
    {
        return ob_get_length() ? ob_get_contents() : null;
    }

    /**
     * Starts error handler and stdout buffering.
     */
    private function start_error_and_output_buffering(Call $call): void
    {
        $error_reporting = $call->get_error_reporting_level() ?: $this->error_reporting_level;
        $this->previous_error_reporting = error_reporting($error_reporting);
        set_error_handler($this->handle_error(...), $error_reporting);
        $this->ob_started = ob_start();
    }

    /**
     * Stops error handler and stdout buffering.
     */
    private function stop_error_and_output_buffering(): void
    {
        if ($this->ob_started) {
            ob_end_clean();
        }
        restore_error_handler();
        if (null !== $this->previous_error_reporting) {
            error_reporting($this->previous_error_reporting);
            $this->previous_error_reporting = null;
        }
    }

    /**
     * Checks if provided error level is not reportable.
     *
     * @param int $level
     */
    private function error_level_is_not_reportable($level): bool
    {
        return !(error_reporting() & $level);
    }
}
