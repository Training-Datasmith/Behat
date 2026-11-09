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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

final class File_Output_Factory extends Output_Factory
{
    public function create_output($stream = null): OutputInterface
    {
        if ($this->get_output_path() === null) {
            throw new Missing_Output_Path_Exception('The `output_path` option must be specified.');
        }
        $file_name = $this->get_output_path();
        if (is_dir($file_name)) {
            throw new Bad_Output_Path_Exception('A file name expected for the `output_path` option, but a directory was given.', $this->get_output_path());
        }
        $dir = dirname($file_name);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = fopen($file_name, 'w');
        if ($file === false) {
            throw new Bad_Output_Path_Exception('The file named in the `output_path` option could not be opened.', $this->get_output_path());
        }

        return new StreamOutput($file, StreamOutput::VERBOSITY_NORMAL, false);
    }
}
