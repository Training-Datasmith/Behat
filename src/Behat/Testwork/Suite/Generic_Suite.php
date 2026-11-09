<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Exception\Parameter_Not_Found_Exception;

/**
 * Represents generic (no specific attributes) test suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Generic_Suite implements Suite
{
    /**
     * Initializes suite.
     *
     * @param string $name
     */
    public function __construct(private $name, private array $settings)
    {
    }

    /**
     * Returns unique suite name.
     *
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * Returns suite settings.
     */
    public function get_settings(): array
    {
        return $this->settings;
    }

    /**
     * Checks if a setting with provided name exists.
     *
     * @param string $key
     */
    public function has_setting($key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Returns setting value by its key.
     *
     * @param string $key
     *
     * @throws Parameter_Not_Found_Exception If setting is not set
     */
    public function get_setting($key)
    {
        if (!$this->has_setting($key)) {
            throw new Parameter_Not_Found_Exception(sprintf('`%s` suite does not have a `%s` setting.', $this->get_name(), $key), $this->get_name(), $key);
        }

        return $this->settings[$key];
    }
}
