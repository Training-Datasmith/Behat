<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Output_Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures formatters based on user input.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Output_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private readonly Output_Manager $manager)
    {
    }

    public function configure(Command $command): void
    {
        $command->addOption('--format', '-f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'How to format tests output. <comment>pretty</comment> is default.' . PHP_EOL . 'Available formats are:' . PHP_EOL . $this->get_formatter_descriptions() . 'You can use multiple formats at the same time.')->addOption('--out', '-o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Write format output to a file/directory instead of' . PHP_EOL . 'STDOUT <comment>(output_path)</comment>. You can also provide different' . PHP_EOL . 'outputs to multiple formats. This option is mandatory for the junit formatter.')->addOption('--format-settings', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Set formatters parameters using json object.' . PHP_EOL . 'Keys are parameter names, values are values.');
    }

    public function execute(InputInterface $input, OutputInterface $output): null
    {
        $formats = $input->getOption('format');
        $outputs = $input->getOption('out');
        $this->configure_formatters($formats, $input, $output);
        $this->configure_outputs($formats, $outputs, $output->isDecorated());

        return null;
    }

    /**
     * Configures formatters based on container, input and output configurations.
     */
    protected function configure_formatters(array $formats, InputInterface $input, OutputInterface $output)
    {
        $this->enable_formatters($formats);
        $this->set_formatters_parameters($input, $output);
    }

    /**
     * Enables formatters.
     */
    protected function enable_formatters(array $formats)
    {
        if (count($formats)) {
            $this->manager->disable_all_formatters();
            foreach ($formats as $format) {
                $this->manager->enable_formatter($format);
            }
        }
    }

    /**
     * Sets formatters parameters based on input & output.
     */
    protected function set_formatters_parameters(InputInterface $input, OutputInterface $output)
    {
        $this->manager->set_formatters_parameter('output_decorate', $output->isDecorated());
        if ($input->getOption('format-settings')) {
            foreach ($input->getOption('format-settings') as $json_settings) {
                $this->load_json_settings($json_settings);
            }
        }
    }

    /**
     * Locates output path from relative one.
     *
     * @param string $path
     *
     * @return string
     */
    protected function locate_output_path($path)
    {
        if ($this->is_absolute_path($path)) {
            return $path;
        }
        $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        if (!file_exists($path)) {
            touch($path);
            $path = realpath($path);
            unlink($path);
        } else {
            $path = realpath($path);
        }

        return $path;
    }

    /**
     * Initializes multiple formatters with different outputs.
     */
    private function configure_outputs(array $formats, array $outputs, bool $decorated = false): void
    {
        if (1 == count($outputs) && !$this->is_standard_output($outputs[0])) {
            $output_path = $this->locate_output_path($outputs[0]);
            $this->manager->set_formatters_parameter('output_path', $output_path);
            $this->manager->set_formatters_parameter('output_decorate', $decorated);

            return;
        }
        foreach ($outputs as $i => $out) {
            if ($this->is_standard_output($out)) {
                continue;
            }
            $output_path = $this->locate_output_path($out);
            if (isset($formats[$i])) {
                $this->manager->set_formatter_parameter($formats[$i], 'output_path', $output_path);
                $this->manager->set_formatter_parameter($formats[$i], 'output_decorate', $decorated);
            }
        }
    }

    /**
     * Checks if provided output identifier represents standard output.
     */
    private function is_standard_output($output_id): bool
    {
        return 'std' === $output_id || 'null' === $output_id || 'false' === $output_id;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     */
    private function is_absolute_path($file): bool
    {
        return $file[0] == '/' || $file[0] == '\\' || strlen($file) > 3 && ctype_alpha($file[0]) && $file[1] == ':' && ($file[2] == '\\' || $file[2] == '/') || null !== parse_url($file, PHP_URL_SCHEME);
    }

    /**
     * Returns formatters description.
     */
    private function get_formatter_descriptions(): string
    {
        return implode(PHP_EOL, array_map(function (Formatter $formatter): string {
            $comment = '- <comment>' . $formatter->get_name() . '</comment>: ';

            return $comment . $formatter->get_description();
        }, $this->manager->get_formatters())) . PHP_EOL;
    }

    /**
     * Loads JSON settings as formatter parameters.
     */
    private function load_json_settings($json_settings): void
    {
        $settings = @json_decode((string) $json_settings, true);
        if (!is_array($settings)) {
            return;
        }
        foreach ($settings as $name => $value) {
            $this->manager->set_formatters_parameter($name, $value);
        }
    }
}
