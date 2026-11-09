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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function configure(SymfonyCommand $command): void
    {
        $command->addOption('--snippets-for', null, InputOption::VALUE_OPTIONAL, 'Specifies which context class to generate snippets for.')->addOption('--snippets-type', null, InputOption::VALUE_REQUIRED, 'Specifies which type of snippets (turnip, regex) to generate.');
    }

    public function execute(InputInterface $input, OutputInterface $output): null
    {
        $this->generator->set_context_identifier(new Aggregate_Context_Identifier([new Context_Interface_Based_Context_Identifier(), new Fixed_Context_Identifier($input->getOption('snippets-for')), new Interactive_Context_Identifier($this->translator, $input, $output)]));
        $this->generator->set_pattern_identifier(new Aggregate_Pattern_Identifier([new Context_Interface_Based_Pattern_Identifier(), new Fixed_Pattern_Identifier($input->getOption('snippets-type'))]));

        return null;
    }
}
