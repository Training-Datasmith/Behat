<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Cli;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Represents Testwork Console Controller.
 *
 * All testwork console controllers should implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Controller
{
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Symfony_Command $command);
    /**
     * Executes controller.
     *
     * @return int|null
     */
    public function execute(Input_Interface $input, Output_Interface $output);
}