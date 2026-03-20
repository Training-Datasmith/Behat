<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Call\Exception;

use ErrorException;
use ParseError;
use ReflectionProperty;
use Throwable;
use TypeError;
/**
 * Fatal Throwable Error.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Fatal_Throwable_Error extends ErrorException
{
    public function __construct(Throwable $e)
    {
        if ($e instanceof ParseError) {
            $message = 'Parse error: ' . $e->get_message();
            $severity = E_PARSE;
        } elseif ($e instanceof TypeError) {
            $message = 'Type error: ' . $e->get_message();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message = 'Fatal error: ' . $e->get_message();
            $severity = E_ERROR;
        }
        parent::__construct($message, $e->get_code(), $severity, $e->get_file(), $e->get_line());
        $this->set_trace($e->get_trace());
    }
    private function set_trace(array $trace): void
    {
        $trace_reflector = new ReflectionProperty('Exception', 'trace');
        $trace_reflector->set_value($this, $trace);
    }
}