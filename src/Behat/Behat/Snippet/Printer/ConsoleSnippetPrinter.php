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

use Behat\Behat\Context\Snippet\Generator\Cannot_Generate_Step_Pattern_Exception;
use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Snippet\Aggregate_Snippet;
use Behat\Gherkin\Node\Step_Node;
use function count;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Behat console-based snippet printer.
 *
 * Extends default printer with default styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Console_Snippet_Printer implements Snippet_Printer
{
    /**
     * Initializes printer.
     */
    public function __construct(private readonly Output_Interface $output, private readonly Translator_Interface $translator)
    {
        $this->output->get_formatter()->set_style('snippet_keyword', new Output_Formatter_Style(null, null, ['bold']));
        $this->output->get_formatter()->set_style('snippet_undefined', new Output_Formatter_Style('yellow'));
        $this->output->get_formatter()->set_style('snippet_failure', new Output_Formatter_Style('red'));
    }
    /**
     * Prints snippets of specific target.
     *
     * @param string             $targetName
     * @param AggregateSnippet[] $snippets
     */
    public function print_snippets($target_name, array $snippets): void
    {
        $message = $this->translator->trans('snippet_proposal_title', ['%count%' => $target_name], 'output');
        $this->output->writeln('--- ' . $message . PHP_EOL);
        $used_classes = [];
        foreach ($snippets as $snippet) {
            foreach ($snippet->get_used_classes() as $used_class) {
                $used_classes[$used_class] = true;
            }
            $this->output->writeln(sprintf('<snippet_undefined>%s</snippet_undefined>', $snippet->get_snippet()) . PHP_EOL);
        }
        $this->output_classes_uses_statements(array_keys($used_classes));
    }
    /**
     * Prints undefined steps of specific suite.
     *
     * @param string     $suiteName
     * @param StepNode[] $steps
     */
    public function print_undefined_steps($suite_name, array $steps): void
    {
        $message = $this->translator->trans('snippet_missing_title', ['%count%' => $suite_name], 'output');
        $this->output->writeln('--- ' . $message . PHP_EOL);
        foreach ($steps as $step) {
            $this->output->writeln(sprintf('    <snippet_undefined>%s %s</snippet_undefined>', $step->get_keyword(), $step->get_text()));
        }
        $this->output->writeln('');
    }
    /**
     * @param array<string> $usedClasses
     */
    public function output_classes_uses_statements(array $used_classes): void
    {
        if ([] === $used_classes) {
            return;
        }
        $message = $this->translator->trans('snippet_proposal_use', ['%count%' => count($used_classes)], 'output');
        $this->output->writeln('--- ' . $message . PHP_EOL);
        foreach ($used_classes as $used_class) {
            $this->output->writeln(sprintf('    <snippet_undefined>use %s;</snippet_undefined>', $used_class));
        }
    }
    /**
     * @param array<CannotGenerateStepPatternException> $exceptions
     */
    public function print_snippet_generation_failures(array $exceptions): void
    {
        if ([] === $exceptions) {
            return;
        }
        $title = $this->translator->trans('snippet_generation_failure_title', [], 'output');
        $hint = $this->translator->trans('snippet_generation_failure_hint', [], 'output');
        $this->output->writeln('<snippet_failure>--- ' . $title . '</snippet_failure>');
        $this->output->writeln('<snippet_failure>    ' . $hint . '</snippet_failure>');
        $this->output->writeln('');
        foreach ($exceptions as $exception) {
            $this->output->writeln('<snippet_failure>    - ' . $exception->step_text . '</snippet_failure>');
        }
    }
}