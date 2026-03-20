<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output\Printer;

/**
 * Isolates all console/filesystem writing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Output_Printer
{
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    public const VERBOSITY_NORMAL = 1;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    public const VERBOSITY_VERBOSE = 2;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    public const VERBOSITY_VERY_VERBOSE = 3;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    public const VERBOSITY_DEBUG = 4;
    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function set_output_path($path);
    /**
     * Returns output path.
     *
     * @return string|null
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function get_output_path();
    /**
     * Sets output styles.
     */
    public function set_output_styles(array $styles);
    /**
     * Returns output styles.
     *
     * @return array
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function get_output_styles();
    /**
     * Forces output to be decorated.
     *
     * @param bool $decorated
     */
    public function set_output_decorated($decorated);
    /**
     * Returns output decoration status.
     *
     * @return bool|null
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function is_output_decorated();
    /**
     * Sets output verbosity level.
     *
     * @param int $level
     */
    public function set_output_verbosity($level);
    /**
     * Returns output verbosity level.
     *
     * @return int
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function get_output_verbosity();
    /**
     * Writes message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function write($messages);
    /**
     * Writes newlined message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln($messages = '');
    /**
     * Clear output stream, so on next write formatter will need to init (create) it again.
     */
    public function flush();
}