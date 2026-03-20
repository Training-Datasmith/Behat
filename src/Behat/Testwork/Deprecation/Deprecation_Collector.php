<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Deprecation;

/**
 * Collects deprecations triggered by Behat code during test execution.
 */
final class Deprecation_Collector
{
    private static ?Deprecation_Collector $instance = null;
    /**
     * @var array<string, array{count: int, file: string, line: int}>
     */
    private array $deprecations = [];
    private int $total_count = 0;
    /** @var callable|null */
    private $previous_handler;
    private bool $is_registered = false;
    /**
     * Returns the singleton instance, creating it if necessary.
     */
    public static function get_instance(): self
    {
        if (!self::$instance instanceof Deprecation_Collector) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public static function trigger(string $message): void
    {
        $instance = self::get_instance();
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = $backtrace[0];
        $file = $caller['file'] ?? 'unknown';
        $line = $caller['line'] ?? 0;
        $instance->record_deprecation($message, $file, $line);
    }
    /**
     * Registers the deprecation error handler.
     */
    public function register(): void
    {
        if ($this->is_registered) {
            return;
        }
        $this->previous_handler = set_error_handler($this->create_error_handler());
        $this->is_registered = true;
    }
    /**
     * Unregisters the deprecation error handler.
     */
    public function unregister(): void
    {
        if (!$this->is_registered) {
            return;
        }
        restore_error_handler();
        $this->is_registered = false;
    }
    /**
     * Returns all collected deprecations with their counts and locations.
     *
     * @return array<string, array{count: int, file: string, line: int}>
     */
    public function get_deprecations(): array
    {
        return $this->deprecations;
    }
    /**
     * Returns the total number of deprecation occurrences.
     */
    public function get_deprecation_count(): int
    {
        return $this->total_count;
    }
    /**
     * Checks if any deprecations were collected.
     */
    public function has_deprecations(): bool
    {
        return $this->total_count > 0;
    }
    /**
     * Creates the error handler that captures deprecations.
     */
    private function create_error_handler(): callable
    {
        return function (int $errno, string $errstr, string $errfile, int $errline): bool {
            if ($errno !== E_USER_DEPRECATED && $errno !== E_DEPRECATED) {
                if ($this->previous_handler !== null) {
                    return ($this->previous_handler)($errno, $errstr, $errfile, $errline);
                }
                return false;
            }
            $this->record_deprecation($errstr, $errfile, $errline);
            return true;
        };
    }
    private function record_deprecation(string $message, string $file, int $line): void
    {
        if (isset($this->deprecations[$message])) {
            ++$this->deprecations[$message]['count'];
        } else {
            $this->deprecations[$message] = ['count' => 1, 'file' => $file, 'line' => $line];
        }
        ++$this->total_count;
    }
}