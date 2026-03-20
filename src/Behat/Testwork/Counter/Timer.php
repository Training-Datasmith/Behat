<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Counter;

use Behat\Testwork\Counter\Exception\Timer_Exception;
use Stringable;
/**
 * Provides time counting functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Timer implements Stringable
{
    private ?float $star_time = null;
    private ?float $stop_time = null;
    /**
     * Starts timer.
     */
    public function start(): void
    {
        $this->star_time = microtime(true);
    }
    /**
     * Stops timer.
     *
     * @throws TimerException If timer has not been started
     */
    public function stop(): void
    {
        if (!$this->star_time) {
            throw new Timer_Exception('You can not stop timer that has not been started.');
        }
        $this->stop_time = microtime(true);
    }
    /**
     * @return float
     *
     * @throws TimerException If timer has not been started
     */
    public function get_time(): int|float
    {
        if (!$this->star_time) {
            throw new Timer_Exception('You can not get time from timer that never been started.');
        }
        $stop_time = $this->stop_time;
        if (!$this->stop_time) {
            $stop_time = microtime(true);
        }
        return $stop_time - $this->star_time;
    }
    /**
     * Returns number of minutes passed.
     */
    public function get_minutes(): int
    {
        return intval(floor($this->get_time() / 60));
    }
    /**
     * Returns number of seconds passed.
     */
    public function get_seconds(): float
    {
        return round($this->get_time() - $this->get_minutes() * 60, 3);
    }
    /**
     * Returns string representation of time passed.
     */
    public function __toString(): string
    {
        if (!$this->star_time || !$this->stop_time) {
            return '0m0s';
        }
        return sprintf('%dm%.2fs', $this->get_minutes(), $this->get_seconds());
    }
}