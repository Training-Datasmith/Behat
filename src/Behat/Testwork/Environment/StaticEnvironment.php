<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Environment;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;
/**
 * Represents static calls environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Static_Environment implements Environment
{
    /**
     * Initializes environment.
     */
    public function __construct(private readonly Suite $suite)
    {
    }
    final public function get_suite(): Suite
    {
        return $this->suite;
    }
    final public function bind_callee(Callee $callee)
    {
        return $callee->get_callable();
    }
}