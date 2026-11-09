<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Printer\Factory\Output_Factory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Stream_Output_Printer implements Output_Printer
{
    private ?OutputInterface $output = null;

    public function __construct(private readonly Output_Factory $output_factory)
    {
    }

    protected function get_output_factory(): Output_Factory
    {
        return $this->output_factory;
    }

    public function set_output_path($path): void
    {
        $this->output_factory->set_output_path($path);
        $this->flush();
    }

    public function get_output_path()
    {
        return $this->output_factory->get_output_path();
    }

    public function set_output_styles(array $styles): void
    {
        $this->output_factory->set_output_styles($styles);
        $this->flush();
    }

    public function get_output_styles()
    {
        return $this->output_factory->get_output_styles();
    }

    public function set_output_decorated($decorated): void
    {
        $this->output_factory->set_output_decorated($decorated);
        $this->flush();
    }

    public function is_output_decorated()
    {
        return $this->output_factory->is_output_decorated();
    }

    public function set_output_verbosity($level): void
    {
        $this->output_factory->set_output_verbosity($level);
        $this->flush();
    }

    public function get_output_verbosity()
    {
        return $this->output_factory->get_output_verbosity();
    }

    public function write($messages): void
    {
        $this->get_writing_stream()->write($messages, false);
    }

    public function writeln($messages = ''): void
    {
        $this->get_writing_stream()->write($messages, true);
    }

    public function flush(): void
    {
        $this->output = null;
    }

    /**
     * Returns output instance, prepared to write.
     *
     * @return OutputInterface
     */
    final protected function get_writing_stream()
    {
        if (!$this->output instanceof OutputInterface) {
            $this->output = $this->output_factory->create_output();
        }

        return $this->output;
    }
}
