<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Ordering\Exception\Invalid_Order_Exception;
use Behat\Testwork\Ordering\Ordered_Exercise;
use Behat\Testwork\Ordering\Orderer\Orderer;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Preloads scenarios and then modifies the order when --order is passed.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class Order_Controller implements Controller
{
    private array $orderers = [];

    public function __construct(EventDispatcherInterface $event_dispatcher, private readonly Ordered_Exercise $exercise)
    {
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(SymfonyCommand $command): void
    {
        $command->addOption('--order', null, InputOption::VALUE_REQUIRED, 'Set an order in which to execute the specifications (this will result in slower feedback).');
    }

    /**
     * Executes controller.
     *
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output): null
    {
        $orderer = $input->getOption('order');
        if (!$orderer) {
            return null;
        }
        if (!array_key_exists($orderer, $this->orderers)) {
            throw new Invalid_Order_Exception(sprintf("Order option '%s' was not recognised", $orderer));
        }
        $this->exercise->set_orderer($this->orderers[$orderer]);

        return null;
    }

    /**
     * Register a new available controller.
     */
    public function register_orderer(Orderer $orderer): void
    {
        $this->orderers[$orderer->get_name()] = $orderer;
    }
}
