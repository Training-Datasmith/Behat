<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Cli;

use Behat\Behat\Context\Environment\Context_Environment;
use Behat\Behat\Context\Snippet\Generator\Target_Context_Identifier;
use Behat\Behat\Definition\Translator\Translator_Interface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Interactive identifier that asks user for input.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Interactive_Context_Identifier implements Target_Context_Identifier
{
    /**
     * Initialises identifier.
     */
    public function __construct(private Translator_Interface $translator, private InputInterface $input, private OutputInterface $output)
    {
    }

    public function guess_target_context_class(Context_Environment $environment): ?string
    {
        if (!$this->input->isInteractive()) {
            return null;
        }
        $suite_name = $environment->get_suite()->get_name();
        $context_classes = $environment->get_context_classes();
        if (!count($context_classes)) {
            return null;
        }
        $message = $this->translator->trans('snippet_context_choice', ['%count%' => $suite_name], 'output');
        $choices = array_merge(['None'], $context_classes);
        $default = '1';
        $answer = $this->ask_question('>> ' . $message, $choices, $default);

        return 'None' !== $answer ? $answer : null;
    }

    /**
     * Asks user question.
     *
     * @param string[] $choices
     */
    private function ask_question(string $message, array $choices, string $default): string
    {
        $this->output->writeln('');
        $helper = new QuestionHelper();
        $question = new ChoiceQuestion(' ' . $message . "\n", $choices, $default);
        $result = $helper->ask($this->input, $this->output, $question);
        assert(is_string($result), 'Answer should be a string - all choices were strings');

        return $result;
    }
}
