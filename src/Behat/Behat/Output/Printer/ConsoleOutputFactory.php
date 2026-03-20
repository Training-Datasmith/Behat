<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Printer;

use Behat\Behat\Output\Printer\Formatter\Console_Formatter;
use Behat\Testwork\Output\Printer\Factory\Console_Output_Factory as BaseFactory;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
/**
 * Extends default printer with default styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Console_Output_Factory extends Base_Factory
{
    protected function create_output_formatter(): Console_Formatter
    {
        $formatter = new Console_Formatter($this->is_output_decorated());
        foreach ($this->get_default_styles() as $name => $style) {
            $formatter->set_style($name, $style);
        }
        return $formatter;
    }
    /**
     * Returns default styles.
     *
     * @return array<string, OutputFormatterStyle>
     */
    private function get_default_styles(): array
    {
        return ['keyword' => new Output_Formatter_Style(null, null, ['bold']), 'stdout' => new Output_Formatter_Style(null, null, []), 'exception' => new Output_Formatter_Style('red'), 'undefined' => new Output_Formatter_Style('yellow'), 'pending' => new Output_Formatter_Style('yellow'), 'pending_param' => new Output_Formatter_Style('yellow', null, ['bold']), 'failed' => new Output_Formatter_Style('red'), 'failed_param' => new Output_Formatter_Style('red', null, ['bold']), 'passed' => new Output_Formatter_Style('green'), 'passed_param' => new Output_Formatter_Style('green', null, ['bold']), 'skipped' => new Output_Formatter_Style('cyan'), 'skipped_param' => new Output_Formatter_Style('cyan', null, ['bold']), 'comment' => new Output_Formatter_Style('black'), 'tag' => new Output_Formatter_Style('cyan')];
    }
}