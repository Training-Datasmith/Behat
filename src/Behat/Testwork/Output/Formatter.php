<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Represents Testwork output formatter.
 *
 * @see Output_Manager
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Formatter extends EventSubscriberInterface
{
    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function get_name();

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function get_description();

    /**
     * Returns formatter output printer.
     *
     * @return Output_Printer
     */
    public function get_output_printer();

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     */
    public function set_parameter($name, $value);

    /**
     * Returns parameter name.
     *
     * @param string $name
     */
    public function get_parameter($name);
}
