<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Translator\Cli;

use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Translation\Translator;
/**
 * Configures translator service to use custom locale.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Language_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Translator $translator)
    {
    }
    public function configure(Command $command): void
    {
        $command->add_option('--lang', null, Input_Option::VALUE_REQUIRED, 'Print output in particular language.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        if (!$input->get_option('lang')) {
            return null;
        }
        $this->translator->set_locale($input->get_option('lang'));
        return null;
    }
}