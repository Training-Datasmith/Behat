<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Exception;

use Behat\Testwork\Call\Exception\Fatal_Throwable_Error;
use Behat\Testwork\Exception\Stringer\Exception_Stringer;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Exception;
use Symfony\Component\Console\Output\Output_Interface;
use Throwable;
/**
 * Presents exceptions as strings using registered stringers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Exception_Presenter
{
    /**
     * @var ExceptionStringer[]
     */
    private array $stringers = [];
    private readonly Configurable_Path_Printer $configurable_path_printer;
    /**
     * Initializes presenter.
     *
     * @param ?string $basePath deprecated, will be removed in next major version
     */
    public function __construct(?string $base_path = null, private int $default_verbosity = Output_Printer::VERBOSITY_NORMAL, ?Configurable_Path_Printer $configurable_path_printer = null)
    {
        // Historically, this class accepted a null (or not present) value for basePath. This was never passed by Behat,
        // but was used by third parties to force the class to render exception traces with absolute file paths.
        // The ConfigurablePathPrinter requires a value, but it is not used if printAbsolutePaths is true.
        // Therefore, if the user provided null we can safely cast to '' (the working directory) and tell the printer
        // to show absolute paths.
        $this->configurable_path_printer = $configurable_path_printer ?? new Configurable_Path_Printer($base_path ?? '', printAbsolutePaths: $base_path === null);
    }
    /**
     * Registers exception stringer.
     */
    public function register_exception_stringer(Exception_Stringer $stringer): void
    {
        $this->stringers[] = $stringer;
    }
    /**
     * Sets default verbosity to a specified level.
     */
    public function set_default_verbosity(int $default_verbosity): void
    {
        $this->default_verbosity = $default_verbosity;
    }
    /**
     * Presents exception as a string.
     */
    public function present_exception(Throwable $exception, ?int $verbosity = null, bool $apply_editor_url = true): string
    {
        $verbosity = $verbosity ?: $this->default_verbosity;
        if (!$exception instanceof Exception) {
            $exception = new Fatal_Throwable_Error($exception);
        }
        foreach ($this->stringers as $stringer) {
            if ($stringer->supports_exception($exception)) {
                return $this->configurable_path_printer->process_paths_in_text($stringer->string_exception($exception, $verbosity), applyEditorUrl: $apply_editor_url);
            }
        }
        if (Output_Interface::VERBOSITY_VERY_VERBOSE <= $verbosity) {
            if (Output_Interface::VERBOSITY_DEBUG > $verbosity) {
                $exception = $this->remove_behat_calls_from_trace($exception);
            }
            return $this->configurable_path_printer->process_paths_in_text(trim((string) $exception));
        }
        return trim($this->configurable_path_printer->process_paths_in_text($exception->get_message()) . ' (' . $exception::class . ')');
    }
    private function remove_behat_calls_from_trace(Exception $exception): string
    {
        $trace_output = '';
        foreach ($exception->get_trace() as $i => $trace) {
            if (isset($trace['file']) && str_contains(str_replace('\\', '/', $trace['file']), 'Behat/Testwork/Call/Handler/RuntimeCallHandler')) {
                break;
            }
            $trace_output .= sprintf('#%d %s: %s()' . PHP_EOL, $i, isset($trace['file']) ? $trace['file'] . '(' . $trace['line'] . ')' : '[internal function]', isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function']);
        }
        return sprintf('%s: %s in %s:%d%sStack trace:%s%s', $exception::class, $exception->get_message(), $exception->get_file(), $exception->get_line(), PHP_EOL, PHP_EOL, $trace_output);
    }
}