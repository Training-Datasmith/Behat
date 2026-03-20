<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Autoloader\Cli;

use Behat\Testwork\Cli\Controller;
use Composer\Autoload\Class_Loader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Registers Testwork autoloader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Autoloader_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Class_Loader $loader)
    {
    }
    public function configure(Command $command): void
    {
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        $this->loader->register();
        return null;
    }
}