<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Tester\Handler\Stop_On_Failure_Handler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Stops tests on first scenario failure.
 *
 * TODO this should be moved in the Behat\Testwork\Tester\Cli namespace
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Stop_On_Failure_Controller implements Controller
{
    private \Behat\Testwork\Tester\Handler\Stop_On_Failure_Handler $stop_on_failure_handler;
    /**
     * @required
     */
    public function set_stop_on_failure_handler(Stop_On_Failure_Handler $stop_on_failure_handler): void
    {
        $this->stop_on_failure_handler = $stop_on_failure_handler;
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->add_option('--stop-on-failure', null, Input_Option::VALUE_NONE, 'Stop processing on first failed scenario.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        if (!$input->get_option('stop-on-failure')) {
            return null;
        }
        $this->stop_on_failure_handler->register_listeners();
        return null;
    }
}