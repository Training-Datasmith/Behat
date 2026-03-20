<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Behat\Config\Formatter;

use Php_Parser\Node\Expr;
final class Json_Formatter extends Formatter
{
    public const NAME = 'json';
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