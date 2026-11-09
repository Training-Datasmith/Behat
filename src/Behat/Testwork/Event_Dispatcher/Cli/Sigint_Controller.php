<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Event_Dispatcher\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Aborted;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Aborts exercise on SIGINT signal.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Sigint_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private EventDispatcherInterface $event_dispatcher)
    {
    }

    public function configure(Command $command): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output): null
    {
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, $this->abort_exercise(...));
        }

        return null;
    }

    /**
     * Dispatches AFTER exercise event and exits program.
     */
    public function abort_exercise(int $signal = SIGINT): never
    {
        $this->event_dispatcher->dispatch(new After_Exercise_Aborted(), Exercise_Completed::AFTER);
        exit(128 + $signal);
    }
}
