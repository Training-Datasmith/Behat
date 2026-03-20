<?php

declare (strict_types=1);
namespace Behat\Config\Formatter;

use Php_Parser\Node\Expr;
final class Progress_Formatter extends Formatter
{
    public const NAME = 'progress';
    private const TIMER_SETTING = 'timer';
    private const SHORT_SUMMARY_SETTING = 'short_summary';
    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param ShowOutputOption $showOutput show the test stdout output as part of the
     *                                     formatter output (yes, no, on-fail, in-summary)
     * @param bool $shortSummary if we should print the short summary which just lists scenarios
     *                            or the long summary which lists steps
     */
    public function __construct(bool $timer = true, Show_Output_Option $show_output = Show_Output_Option::InSummary, bool $short_summary = false, ...$base_options)
    {
        $settings = [self::TIMER_SETTING => $timer, Show_Output_Option::OPTION_NAME => $show_output->value, self::SHORT_SUMMARY_SETTING => $short_summary];
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