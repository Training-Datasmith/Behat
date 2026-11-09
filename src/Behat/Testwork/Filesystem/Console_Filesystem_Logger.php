<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Filesystem;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Logs filesystem operations to the console.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Console_Filesystem_Logger implements Filesystem_Logger
{
    /**
     * Initializes logger.
     */
    public function __construct(private $base_path, private readonly OutputInterface $output)
    {
    }

    public function directory_created($path, $reason): void
    {
        $this->output->writeln(sprintf('<info>+d</info> %s - %s', str_replace($this->base_path . DIRECTORY_SEPARATOR, '', realpath($path)), $reason));
    }

    public function file_created($path, $reason): void
    {
        $this->output->writeln(sprintf('<info>+f</info> %s - %s', str_replace($this->base_path . DIRECTORY_SEPARATOR, '', realpath($path)), $reason));
    }

    public function file_updated($path, $reason): void
    {
        $this->output->writeln(sprintf('<info>u</info> %s - %s', str_replace($this->base_path . DIRECTORY_SEPARATOR, '', realpath($path)), $reason));
    }
}
