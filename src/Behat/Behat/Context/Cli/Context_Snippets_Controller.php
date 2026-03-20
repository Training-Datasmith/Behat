<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Cli;

use Behat\Behat\Context\Snippet\Generator\Aggregate_Context_Identifier;
use Behat\Behat\Context\Snippet\Generator\Aggregate_Pattern_Identifier;
use Behat\Behat\Context\Snippet\Generator\Context_Interface_Based_Context_Identifier;
use Behat\Behat\Context\Snippet\Generator\Context_Interface_Based_Pattern_Identifier;
use Behat\Behat\Context\Snippet\Generator\Context_Snippet_Generator;
use Behat\Behat\Context\Snippet\Generator\Fixed_Context_Identifier;
use Behat\Behat\Context\Snippet\Generator\Fixed_Pattern_Identifier;
use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Configures which context snippets are generated for.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Context_Snippets_Controller implements Controller
{
    /**
     * Initialises controller.
     */
    public function __construct(private Context_Snippet_Generator $generator, private Translator_Interface $translator)
    {
    }
    public function configure(Symfony_Command $command): void
    {
        $command->add_option('--snippets-for', null, Input_Option::VALUE_OPTIONAL, 'Specifies which context class to generate snippets for.')->add_option('--snippets-type', null, Input_Option::VALUE_REQUIRED, 'Specifies which type of snippets (turnip, regex) to generate.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        $this->generator->set_context_identifier(new Aggregate_Context_Identifier([new Context_Interface_Based_Context_Identifier(), new Fixed_Context_Identifier($input->get_option('snippets-for')), new Interactive_Context_Identifier($this->translator, $input, $output)]));
        $this->generator->set_pattern_identifier(new Aggregate_Pattern_Identifier([new Context_Interface_Based_Pattern_Identifier(), new Fixed_Pattern_Identifier($input->get_option('snippets-type'))]));
        return null;
    }
}