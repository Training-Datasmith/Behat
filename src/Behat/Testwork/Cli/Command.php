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

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Extends Symfony console command with a controller-based delegation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Command extends Base_Command
{
    /**
     * Initializes command.
     *
     * @param string       $commandName
     * @param Controller[] $controllers
     */
    public function __construct(?string $command_name, private readonly array $controllers)
    {
        parent::__construct($command_name);
    }
    /**
     * Configures the command by running controllers prepare().
     */
    protected function configure(): void
    {
        foreach ($this->controllers as $controller) {
            $controller->configure($this);
        }
    }
    /**
     * Executes the current command by executing all controllers action().
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int Return code of one of the processors or 0 if none of them returned integer
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        foreach ($this->controllers as $controller) {
            if (is_int($return = $controller->execute($input, $output))) {
                return $return;
            }
        }
        return 0;
    }
}