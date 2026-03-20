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

use Behat\Testwork\Suite\Suite;
/**
 * Prints simple definitions list.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Console_Definition_List_Printer extends Console_Definition_Printer
{
    public function print_definitions(Suite $suite, $definitions): void
    {
        $output = [];
        foreach ($definitions as $definition) {
            $definition = $this->translate_definition($suite, $definition);
            $output[] = strtr('{suite} <def_dimmed>|</def_dimmed> <info>{type}</info> <def_regex>{regex}</def_regex>', ['{suite}' => $suite->get_name(), '{type}' => $this->get_definition_type($definition, true), '{regex}' => $definition->get_pattern()]);
        }
        $this->write(rtrim(implode(PHP_EOL, $output)));
    }
}