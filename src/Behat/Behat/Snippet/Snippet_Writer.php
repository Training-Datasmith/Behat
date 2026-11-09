<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Behat\Snippet\Appender\Snippet_Appender;
use Behat\Behat\Snippet\Printer\Snippet_Printer;

/**
 * Prints or appends snippets to a specific environment using registered appenders and printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Snippet_Writer
{
    /**
     * @var Snippet_Appender[]
     */
    private array $appenders = [];

    /**
     * Registers snippet appender.
     */
    public function register_snippet_appender(Snippet_Appender $appender): void
    {
        $this->appenders[] = $appender;
    }

    /**
     * Appends snippets to appropriate targets.
     *
     * @param Aggregate_Snippet[] $snippets
     */
    public function append_snippets(array $snippets): void
    {
        foreach ($snippets as $snippet) {
            $this->append_snippet($snippet);
        }
    }

    /**
     * Prints snippets using provided printer.
     *
     * @param Aggregate_Snippet[] $snippets
     */
    public function print_snippets(Snippet_Printer $printer, array $snippets): void
    {
        $printable_snippets = [];
        foreach ($snippets as $snippet) {
            foreach ($snippet->get_targets() as $target) {
                $target_snippets = [];
                if (isset($printable_snippets[$target])) {
                    $target_snippets = $printable_snippets[$target];
                }
                $target_snippets[] = $snippet;
                $printable_snippets[$target] = $target_snippets;
            }
        }
        foreach ($printable_snippets as $target => $target_snippets) {
            $printer->print_snippets($target, $target_snippets);
        }
    }

    /**
     * Prints undefined steps using provided printer.
     */
    public function print_undefined_steps(Snippet_Printer $printer, array $undefined_steps): void
    {
        $printable_steps = [];
        foreach ($undefined_steps as $undefined_step) {
            $suite_name = $undefined_step->get_environment()->get_suite()->get_name();
            $step = $undefined_step->get_step();
            if (!isset($printable_steps[$suite_name])) {
                $printable_steps[$suite_name] = [];
            }
            $printable_steps[$suite_name][$step->getText()] = $step;
        }
        foreach ($printable_steps as $suite_name => $steps) {
            $printer->print_undefined_steps($suite_name, array_values($steps));
        }
    }

    /**
     * Appends snippet to appropriate targets.
     */
    private function append_snippet(Aggregate_Snippet $snippet): void
    {
        foreach ($this->appenders as $appender) {
            if (!$appender->supports_snippet($snippet)) {
                continue;
            }
            $appender->append_snippet($snippet);
        }
    }
}
