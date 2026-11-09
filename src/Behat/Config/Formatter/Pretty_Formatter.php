<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class Pretty_Formatter extends Formatter
{
    public const NAME = 'pretty';
    public const PRINT_SKIPPED_STEPS_PARAMETER_NAME = 'printSkippedSteps';
    private const TIMER_SETTING = 'timer';
    private const EXPAND_SETTING = 'expand';
    private const PATHS_SETTING = 'paths';
    private const MULTILINE_SETTING = 'multiline';
    private const SHORT_SUMMARY_SETTING = 'short_summary';
    public const PRINT_SKIPPED_STEPS_SETTING = 'print_skipped_steps';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param bool $expand print each example of a scenario outline separately
     * @param bool $paths display the file path and line number for each scenario
     *                    and the context file and method for each step
     * @param bool $multiline print out PyStrings and TableNodes in full
     */
    public function __construct(bool $timer = true, bool $expand = false, bool $paths = true, bool $multiline = true, Show_Output_Option $showOutput = Show_Output_Option::Yes, bool $shortSummary = true, bool $printSkippedSteps = true, ...$base_options)
    {
        $settings = [self::TIMER_SETTING => $timer, self::EXPAND_SETTING => $expand, self::PATHS_SETTING => $paths, self::MULTILINE_SETTING => $multiline, Show_Output_Option::OPTION_NAME => $showOutput->value, self::SHORT_SUMMARY_SETTING => $shortSummary, self::PRINT_SKIPPED_STEPS_SETTING => $printSkippedSteps];
        $settings = [...$settings, ...$base_options];
        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return (new self())->to_array();
    }

    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        return $this->to_php_expr_for_named_formatter();
    }
}
