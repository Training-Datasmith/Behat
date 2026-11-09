<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Context_Class;

use Behat\Testwork\Suite\Suite;

/**
 * Generates context classes (as a string).
 *
 * @see Suite_With_Contexts_Setup
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Class_Generator
{
    /**
     * Checks if generator supports provided context class.
     *
     * @return bool
     */
    public function supports_suite_and_class(Suite $suite, $context_class);

    /**
     * Generates context class code.
     *
     * @return string The context class source code
     */
    public function generate_class(Suite $suite, $context_class);
}
