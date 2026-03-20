<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output\Printer\Factory;

use Behat\Testwork\Output\Exception\Bad_Output_Path_Exception;
use Symfony\Component\Console\Formatter\Output_Formatter;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Output\Stream_Output;
/**
 * Creates an output stream for the console.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Console_Output_Factory extends Output_Factory
{
    /**
     * Creates output formatter that is used to create a stream.
     */
    protected function create_output_formatter(): \Symfony\Component\Console\Formatter\Output_Formatter
    {
        return new Output_Formatter();
    }
    /**
     * Configure output stream parameters.
     */
    protected function configure_output_stream(Output_Interface $output)
    {
        $verbosity = $this->get_output_verbosity() ? Output_Interface::VERBOSITY_VERBOSE : Output_Interface::VERBOSITY_NORMAL;
        $output->set_verbosity($verbosity);
        if (null !== $this->is_output_decorated()) {
            $output->get_formatter()->set_decorated($this->is_output_decorated());
        }
    }
    /**
     * Returns new output stream.
     *
     * Override this method & call flush() to write output in another stream
     *
     * @return resource
     *
     * @throws BadOutputPathException
     */
    protected function create_output_stream()
    {
        if (null === $this->get_output_path()) {
            $stream = fopen('php://stdout', 'w');
        } elseif (!is_dir($this->get_output_path())) {
            $stream = fopen($this->get_output_path(), 'w');
        } else {
            throw new Bad_Output_Path_Exception(sprintf('Filename expected as `output_path` parameter, but got `%s`.', $this->get_output_path()), $this->get_output_path());
        }
        return $stream;
    }
    public function create_output($stream = null): \Symfony\Component\Console\Output\Stream_Output
    {
        $stream = $stream ?: $this->create_output_stream();
        $format = $this->create_output_formatter();
        // set user-defined styles
        foreach ($this->get_output_styles() as $name => $options) {
            $style = new Output_Formatter_Style();
            if (isset($options[0])) {
                $style->set_foreground($options[0]);
            }
            if (isset($options[1])) {
                $style->set_background($options[1]);
            }
            if (isset($options[2])) {
                $style->set_options($options[2]);
            }
            $format->set_style($name, $style);
        }
        $output = new Stream_Output($stream, Stream_Output::VERBOSITY_NORMAL, $this->is_output_decorated(), $format);
        $this->configure_output_stream($output);
        return $output;
    }
}