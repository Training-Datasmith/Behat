<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Testwork\Output\Printer\Output_Printer;

/**
 * Behat counter printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Counter_Printer
{
    /**
     * Initializes printer.
     */
    public function __construct(private Result_To_String_Converter $result_converter, private Translator_Interface $translator)
    {
    }

    /**
     * Prints scenario and step counters.
     *
     * @param string        $intro
     */
    public function print_counters(Output_Printer $printer, $intro, array $stats): void
    {
        $stats = array_filter($stats, fn ($count): bool => 0 !== $count);
        $total_count = 0 === count($stats) ? 0 : array_sum($stats);
        $detailed_stats = [];
        foreach ($stats as $result_code => $count) {
            $style = $this->result_converter->convert_result_code_to_string($result_code);
            $trans_id = $style . '_count';
            $message = $this->translator->trans($trans_id, ['%count%' => $count], 'output');
            $detailed_stats[] = sprintf('{+%s}%s{-%s}', $style, $message, $style);
        }
        $message = $this->translator->trans($intro, ['%count%' => $total_count], 'output');
        $printer->write($message);
        if (count($detailed_stats)) {
            $printer->write(sprintf(' (%s)', implode(', ', $detailed_stats)));
        }
        $printer->writeln();
    }
}
