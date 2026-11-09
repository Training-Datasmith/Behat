<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Factory;

use Behat\Testwork\Output\Exception\Bad_Output_Path_Exception;
use Behat\Testwork\Output\Exception\Missing_Output_Path_Exception;
use LogicException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Creates an output stream for the filesystem.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Filesystem_Output_Factory extends Output_Factory
{
    private $file_name;

    public function set_file_name($file_name): void
    {
        $this->file_name = $file_name;
    }

    /**
     * Configure output stream parameters.
     */
    protected function configure_output_stream(OutputInterface $output)
    {
        $verbosity = $this->get_output_verbosity() ? OutputInterface::VERBOSITY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $output->setVerbosity($verbosity);
    }

    public function create_output($stream = null): StreamOutput
    {
        if ($this->get_output_path() === null) {
            throw new Missing_Output_Path_Exception('The `output_path` option must be specified.');
        }
        if (is_file($this->get_output_path())) {
            throw new Bad_Output_Path_Exception('Directory expected for the `output_path` option, but a filename was given.', $this->get_output_path());
        }
        if (!is_dir($this->get_output_path())) {
            mkdir($this->get_output_path(), 0777, true);
        }
        if (null === $this->file_name) {
            throw new LogicException('Unable to create file, no file name specified');
        }
        $file_path = $this->get_output_path() . '/' . $this->file_name;
        $stream = new StreamOutput(fopen($file_path, 'w'), StreamOutput::VERBOSITY_NORMAL, false);
        $this->configure_output_stream($stream);

        return $stream;
    }
}
