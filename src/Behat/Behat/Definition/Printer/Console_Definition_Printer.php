<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Printer;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Translator\Definition_Translator;
use Behat\Gherkin\Keywords\KeywordsInterface;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Represents console-based definition printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Console_Definition_Printer implements Definition_Printer
{
    public function __construct(private readonly OutputInterface $output, Pattern_Transformer $pattern_transformer, private readonly Definition_Translator $translator, private readonly KeywordsInterface $keywords)
    {
        $this->output->getFormatter()->setStyle('def_regex', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('def_regex_capture', new OutputFormatterStyle('yellow', null, ['bold']));
        $this->output->getFormatter()->setStyle('def_dimmed', new OutputFormatterStyle('black', null, ['bold']));
    }

    /**
     * Writes text to the console.
     *
     * @param string $text
     */
    final protected function write(string|iterable $text, bool $line_break_before = false)
    {
        if ($line_break_before) {
            $this->output->writeln('');
        }
        $this->output->writeln($text);
        $this->output->writeln('');
    }

    final protected function get_definition_type(Definition $definition, $only_one = false)
    {
        $this->keywords->setLanguage($this->translator->get_locale());
        $method = 'get' . ucfirst($definition->get_type()) . 'Keywords';
        $keywords = explode('|', (string) $this->keywords->{$method}());
        if ($only_one) {
            return current($keywords);
        }

        return 1 < count($keywords) ? '[' . implode('|', $keywords) . ']' : implode('|', $keywords);
    }

    /**
     * Translates definition using translator.
     *
     * @return Definition
     */
    final protected function translate_definition(Suite $suite, Definition $definition)
    {
        return $this->translator->translate_definition($suite, $definition);
    }

    final protected function translate_info_text(string $info_text, array $parameters): string
    {
        return $this->translator->translate_info_text($info_text, $parameters);
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */
    final protected function is_verbose()
    {
        return $this->output->isVerbose();
    }
}
