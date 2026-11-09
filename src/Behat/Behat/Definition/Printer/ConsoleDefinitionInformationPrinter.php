<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Printer;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Suite\Suite;
/**
 * Prints definitions with full information about them.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Console_Definition_Information_Printer extends Console_Definition_Printer implements Unused_Definition_Printer
{
    /**
     * @var string|null
     */
    private $search_criterion;
    /**
     * Sets search criterion.
     *
     * @param string $criterion
     */
    public function set_search_criterion($criterion): void
    {
        $this->search_criterion = $criterion;
    }
    public function print_definitions(Suite $suite, $definitions): void
    {
        $this->print_definitions_with_optional_suite($definitions, $suite);
    }
    public function print_unused_definitions(array $definitions): void
    {
        $unused_definitions_text = $this->translate_info_text('unused_definitions', ['%count%' => count($definitions)]);
        $this->write('--- ' . $unused_definitions_text, true);
        if (count($definitions) !== 0) {
            $this->print_definitions_with_optional_suite($definitions);
        }
    }
    /**
     * @param Definition[] $definitions
     */
    private function print_definitions_with_optional_suite(array $definitions, ?Suite $suite = null): void
    {
        $search = $this->search_criterion;
        $output = [];
        foreach ($definitions as $definition) {
            if ($suite instanceof Suite) {
                $definition = $this->translate_definition($suite, $definition);
            }
            $pattern = $definition->get_pattern();
            if (null !== $search && false === mb_strpos($pattern, $search, 0, 'utf8')) {
                continue;
            }
            $lines = array_merge($this->extract_header($suite, $definition), $this->extract_description($suite, $definition), $this->extract_footer($suite, $definition));
            $output[] = implode(PHP_EOL, $lines) . PHP_EOL;
        }
        $this->write(rtrim(implode(PHP_EOL, $output)));
    }
    /**
     * Extracts the formatted header from the definition.
     *
     * @return string[]
     */
    private function extract_header(?Suite $suite, Definition $definition): array
    {
        $pattern = $definition->get_pattern();
        $lines = [];
        $indent = $suite instanceof Suite ? '{suite} <def_dimmed>|</def_dimmed> ' : '';
        $lines[] = strtr($indent . '<info>{type}</info> <def_regex>{regex}</def_regex>', ['{suite}' => $suite instanceof Suite ? $suite->get_name() : '', '{type}' => $this->get_definition_type($definition), '{regex}' => $pattern]);
        return $lines;
    }
    /**
     * Extracts the formatted description from the definition.
     *
     * @return string[]
     */
    private function extract_description(?Suite $suite, Definition $definition): array
    {
        $lines = [];
        if ($description = $definition->get_description()) {
            $indent = $suite instanceof Suite ? '{space}<def_dimmed>|</def_dimmed> ' : '';
            foreach (explode("\n", $description) as $description_line) {
                $lines[] = strtr($indent . '{description}', ['{space}' => $suite instanceof Suite ? str_pad('', mb_strlen($suite->get_name(), 'utf8') + 1) : '', '{description}' => $description_line]);
            }
        }
        return $lines;
    }
    /**
     * Extracts the formatted footer from the definition.
     *
     * @return string[]
     */
    private function extract_footer(?Suite $suite, Definition $definition): array
    {
        $lines = [];
        $indent = $suite instanceof Suite ? '{space}<def_dimmed>|</def_dimmed> at ' : '';
        $lines[] = strtr($indent . '`{path}`', ['{space}' => $suite instanceof Suite ? str_pad('', mb_strlen($suite->get_name(), 'utf8') + 1) : '', '{path}' => $definition->get_path()]);
        if ($this->is_verbose()) {
            $indent = $suite instanceof Suite ? '{space}<def_dimmed>|</def_dimmed> on ' : '';
            $lines[] = strtr($indent . '`{filepath}[{start}:{end}]`', ['{space}' => $suite instanceof Suite ? str_pad('', mb_strlen($suite->get_name(), 'utf8') + 1) : '', '{filepath}' => $definition->get_reflection()->get_file_name(), '{start}' => $definition->get_reflection()->get_start_line(), '{end}' => $definition->get_reflection()->get_end_line()]);
        }
        return $lines;
    }
}