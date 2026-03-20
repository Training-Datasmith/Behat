<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Hook\Scope\Hook_Scope;
/**
 * Represents hook stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Hook_Stat
{
    private \Behat\Testwork\Hook\Scope\Hook_Scope $scope = null;
    /**
     * Initializes hook stat.
     *
     * @param string      $name
     * @param string      $path
     * @param string|null $error
     * @param string|null $stdOut
     */
    public function __construct(private $name, private $path, private $error = null, private $std_out = null)
    {
    }
    public function set_scope(Hook_Scope $scope): void
    {
        $this->scope = $scope;
    }
    public function get_name()
    {
        return $this->name;
    }
    public function is_successful(): bool
    {
        return null === $this->error;
    }
    /**
     * Returns hook standard output (if has some).
     *
     * @return string|null
     */
    public function get_std_out()
    {
        return $this->std_out;
    }
    /**
     * Returns hook exception.
     *
     * @return string
     */
    public function get_error()
    {
        return $this->error;
    }
    /**
     * Returns hook path.
     *
     * @return string
     */
    public function get_path()
    {
        return $this->path;
    }
    public function get_scope(): Hook_Scope
    {
        return $this->scope;
    }
}