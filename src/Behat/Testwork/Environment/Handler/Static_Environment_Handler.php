<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Handler;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Static_Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents environment handler based on static calls (without any isolation).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Static_Environment_Handler implements Environment_Handler
{
    public function supports_suite(Suite $suite): bool
    {
        return true;
    }

    public function build_environment(Suite $suite): Environment
    {
        return new Static_Environment($suite);
    }

    public function supports_environment_and_subject(Environment $environment, $test_subject = null): bool
    {
        return $environment instanceof Static_Environment;
    }

    public function isolate_environment(Environment $environment, $test_subject = null): Environment
    {
        return $environment;
    }
}
