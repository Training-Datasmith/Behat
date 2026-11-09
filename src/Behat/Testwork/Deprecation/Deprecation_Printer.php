<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation;

use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints collected deprecations at the end of the test run.
 */
final readonly class Deprecation_Printer
{
    public function __construct(private Deprecation_Collector $collector, private Configurable_Path_Printer $path_printer)
    {
    }

    /**
     * Prints all collected deprecations.
     */
    public function print_deprecations(OutputInterface $output): void
    {
        if (!$this->collector->has_deprecations()) {
            return;
        }
        $deprecations = $this->collector->get_deprecations();
        $total_count = $this->collector->get_deprecation_count();
        $unique_count = count($deprecations);
        $output->writeln('');
        $output->writeln(sprintf('<comment>%d deprecation%s triggered (%d unique):</comment>', $total_count, $total_count === 1 ? '' : 's', $unique_count));
        $output->writeln('');
        foreach ($deprecations as $message => $info) {
            $count_text = $info['count'] > 1 ? sprintf(' <comment>(%dx)</comment>', $info['count']) : '';
            $output->writeln(sprintf('  <comment>⚠</comment> %s%s', $message, $count_text));
            $location = sprintf('%s:%d', $info['file'], $info['line']);
            $processed_location = $this->path_printer->process_paths_in_text($location);
            $output->writeln(sprintf('    <comment>→</comment> %s', $processed_location));
        }
        $output->writeln('');
    }
}
