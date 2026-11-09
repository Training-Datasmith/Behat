<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Path_Options\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures the printing of paths in the output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Path_Options_Controller implements Controller
{
    public function __construct(private readonly Configurable_Path_Printer $configurable_path_printer)
    {
    }

    public function configure(Command $command): void
    {
        $command->addOption('--print-absolute-paths', null, InputOption::VALUE_NONE, 'Print absolute paths in output')->addOption('--editor-url', null, InputOption::VALUE_REQUIRED, 'URL template for opening files in an editor')->addOption('--remove-prefix', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of prefixes to remove from paths');
    }

    public function execute(InputInterface $input, OutputInterface $output): null
    {
        $print_absolute_paths = $input->getOption('print-absolute-paths');
        $editor_url = $input->getOption('editor-url');
        $remove_prefix = $input->getOption('remove-prefix');
        // Parse comma-separated list into an array
        $remove_prefix_array = [];
        if ($remove_prefix !== null) {
            assert(is_string($remove_prefix), '--remove-prefix option should be string or null');
            $remove_prefix_array = explode(',', $remove_prefix);
        }
        $this->configure_print_paths($print_absolute_paths, $editor_url, $remove_prefix_array);

        return null;
    }

    private function configure_print_paths(bool $print_absolute_paths, ?string $editor_url, array $remove_prefix = []): void
    {
        if ($print_absolute_paths) {
            $this->configurable_path_printer->set_print_absolute_paths($print_absolute_paths);
        }
        if ($editor_url !== null) {
            $this->configurable_path_printer->set_editor_url($editor_url);
        }
        if ($remove_prefix !== []) {
            $this->configurable_path_printer->set_remove_prefix($remove_prefix);
        }
    }
}
