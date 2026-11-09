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

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Output_Factory
{
    public const VERBOSITY_NORMAL = 1;
    public const VERBOSITY_VERBOSE = 2;
    public const VERBOSITY_VERY_VERBOSE = 3;
    public const VERBOSITY_DEBUG = 4;
    /**
     * @var string|null
     */
    private $output_path;
    private array $output_styles = [];
    private ?bool $output_decorated = null;
    private int $verbosity_level = 0;

    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function set_output_path($path): void
    {
        $this->output_path = $path;
    }

    /**
     * Returns output path.
     *
     * @return string|null
     */
    public function get_output_path()
    {
        return $this->output_path;
    }

    /**
     * Sets output styles.
     */
    public function set_output_styles(array $styles): void
    {
        $this->output_styles = $styles;
    }

    /**
     * Returns output styles.
     *
     * @return array
     */
    public function get_output_styles()
    {
        return $this->output_styles;
    }

    /**
     * Forces output to be decorated.
     */
    public function set_output_decorated(?bool $decorated): void
    {
        $this->output_decorated = $decorated;
    }

    /**
     * Returns output decoration status.
     *
     * @return bool|null
     */
    public function is_output_decorated()
    {
        return $this->output_decorated;
    }

    /**
     * Sets output verbosity level.
     *
     * @param int $level
     */
    public function set_output_verbosity($level): void
    {
        $this->verbosity_level = intval($level);
    }

    /**
     * Returns output verbosity level.
     *
     * @return int
     */
    public function get_output_verbosity()
    {
        return $this->verbosity_level;
    }

    /**
     * Returns a new output stream.
     *
     * @return OutputInterface
     */
    abstract public function create_output();
}
