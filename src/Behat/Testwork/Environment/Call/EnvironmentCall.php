<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Environment\Call;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
/**
 * Represents environment-based call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Environment_Call implements Call
{
    /**
     * Initializes call.
     *
     * @param int|null $errorReportingLevel
     */
    public function __construct(private readonly Environment $environment, private readonly Callee $callee, private readonly array $arguments, private $error_reporting_level = null)
    {
    }
    /**
     * Returns environment this call is executed from.
     */
    final public function get_environment(): Environment
    {
        return $this->environment;
    }
    final public function get_callee(): Callee
    {
        return $this->callee;
    }
    final public function get_bound_callable()
    {
        return $this->environment->bind_callee($this->callee);
    }
    final public function get_arguments(): array
    {
        return $this->arguments;
    }
    final public function get_error_reporting_level()
    {
        return $this->error_reporting_level;
    }
}