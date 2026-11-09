<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet\Printer;

use Behat\Behat\Snippet\Aggregate_Snippet;
use Behat\Gherkin\Node\Step_Node;
/**
 * Prints all snippets for a target.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Snippet_Printer
{
    /**
     * Prints snippets of the specific target.
     *
     * @param string             $targetName
     * @param AggregateSnippet[] $snippets
     */
    public function print_snippets($target_name, array $snippets);
    /**
     * Prints undefined steps of the specific suite.
     *
     * @param string     $suiteName
     * @param StepNode[] $steps
     */
    public function print_undefined_steps($suite_name, array $steps);
}