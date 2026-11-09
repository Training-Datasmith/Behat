<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class Progress_Formatter extends Formatter
{
    public const NAME = 'progress';
    private const TIMER_SETTING = 'timer';
    private const SHORT_SUMMARY_SETTING = 'short_summary';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     */
    public function __construct(bool $timer = true, Show_Output_Option $showOutput = Show_Output_Option::InSummary, bool $shortSummary = false, ...$base_options)
    {
        $settings = [self::TIMER_SETTING => $timer, Show_Output_Option::OPTION_NAME => $showOutput->value, self::SHORT_SUMMARY_SETTING => $shortSummary];
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
