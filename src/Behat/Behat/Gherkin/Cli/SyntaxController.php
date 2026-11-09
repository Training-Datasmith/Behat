<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Gherkin\Cli;

use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Util\Strict_Regex;
use Behat\Gherkin\Keywords\Keywords_Dumper;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Prints example of the feature to present all available syntax keywords.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Syntax_Controller implements Controller
{
    private Keywords_Dumper $keywords_dumper;
    /**
     * Initializes controller.
     */
    public function __construct(Keywords_Dumper $dumper, private Translator_Interface $translator)
    {
        $dumper->set_keywords_dumper_function($this->dump_keywords(...));
        $this->keywords_dumper = $dumper;
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->add_option('--story-syntax', null, Input_Option::VALUE_NONE, 'Print <comment>*.feature</comment> example.' . PHP_EOL . 'Use <info>--lang</info> to see specific language.');
    }
    /**
     * Executes controller.
     */
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        if (!$input->get_option('story-syntax')) {
            return null;
        }
        $output->get_formatter()->set_style('gherkin_keyword', new Output_Formatter_Style('green', null, ['bold']));
        $output->get_formatter()->set_style('gherkin_comment', new Output_Formatter_Style('yellow'));
        $story = $this->keywords_dumper->dump($this->translator->get_locale());
        $story = Strict_Regex::replace('/^\#.*/', '<gherkin_comment>$0</gherkin_comment>', $story);
        $output->writeln($story);
        $output->writeln('');
        return 0;
    }
    /**
     * Keywords dumper.
     *
     * @param array $keywords keywords list
     */
    public function dump_keywords(array $keywords): string
    {
        $dump = '<gherkin_keyword>' . implode('</gherkin_keyword>|<gherkin_keyword>', $keywords) . '</gherkin_keyword>';
        if (1 < count($keywords)) {
            return '[' . $dump . ']';
        }
        return $dump;
    }
}