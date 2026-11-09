<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Translator\Cli;

use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Translation\Translator;
/**
 * Configures translator service and loads default translations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Gherkin_Translations_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Translator $translator)
    {
    }
    public function configure(Symfony_Command $command): void
    {
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        $i18n_path = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . 'i18n.php';
        foreach (require $i18n_path as $lang => $messages) {
            $this->translator->add_resource('array', $messages, $lang, 'output');
        }
        return null;
    }
}