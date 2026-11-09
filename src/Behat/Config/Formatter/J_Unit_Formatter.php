<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class J_Unit_Formatter extends Formatter
{
    public const NAME = 'junit';
    private const TIMER_SETTING = 'timer';

    /**
     * @param bool $timer include run time attributes in generated report
     */
    public function __construct(bool $timer = true, ...$base_options)
    {
        $settings = [self::TIMER_SETTING => $timer];
        $settings = [...$settings, ...$base_options];
        parent::__construct(name: self::NAME, settings: $settings);
    }

    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        return $this->to_php_expr_for_named_formatter();
    }

    public static function defaults(): array
    {
        return (new self())->to_array();
    }
}
