<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Service_Container\Formatter;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides a way to easily define custom formatters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Formatter_Factory
{
    /**
     * Builds formatter configuration.
     */
    public function build_formatter(ContainerBuilder $container);

    /**
     * Processes formatter configuration.
     */
    public function process_formatter(ContainerBuilder $container);
}
