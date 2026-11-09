<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context;

/**
 * Context that implements this interface is also treated as a translation provider for all it's callees.
 *
 * @see Translatable_Context_Reader
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Translatable_Context extends Context
{
    /**
     * Returns array of Translator-supported resource paths.
     *
     * For instance:
     *
     *  * array(__DIR__.'/../'ru.yml)
     *  * array(__DIR__.'/../'en.xliff)
     *  * array(__DIR__.'/../'de.php)
     *
     * @return string[]
     */
    public static function getTranslationResources();
}
