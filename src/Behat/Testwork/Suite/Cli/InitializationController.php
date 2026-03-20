<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\Suite_Bootstrapper;
use Behat\Testwork\Suite\Suite_Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Initializes registered test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Initialization_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Suite_Repository $repository, private Suite_Bootstrapper $bootstrapper)
    {
    }
    public function configure(Command $command): void
    {
        $command->add_option('--init', null, Input_Option::VALUE_NONE, 'Initialize all registered test suites.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        if (!$input->get_option('init')) {
            return null;
        }
        $suites = $this->repository->get_suites();
        $this->bootstrapper->bootstrap_suites($suites);
        $output->write(PHP_EOL);
        return 0;
    }
}