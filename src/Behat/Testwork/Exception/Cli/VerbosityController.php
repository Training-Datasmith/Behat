<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Exception\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Exception\Exception_Presenter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Controls exception default verbosity level.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Verbosity_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Exception_Presenter $exception_presenter)
    {
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        if ($output->get_verbosity() !== Output_Interface::VERBOSITY_NORMAL) {
            $this->exception_presenter->set_default_verbosity($output->get_verbosity());
        }
        return null;
    }
}