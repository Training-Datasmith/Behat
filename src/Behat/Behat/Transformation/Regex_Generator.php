<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

/**
 * Regular expression generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Regex_Generator
{
    /**
     * Generates regular expression using provided parameters.
     *
     * @param string $pattern
     * @param string $language
     *
     * @return string
     */
    public function generate_regex($suite_name, $pattern, $language);
}
