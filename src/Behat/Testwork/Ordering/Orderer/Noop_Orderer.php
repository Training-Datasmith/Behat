<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Orderer;

/**
 * Null implementation of Orderer that does no ordering.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class Noop_Orderer implements Orderer
{
    public function order(array $scenario_iterators): array
    {
        return $scenario_iterators;
    }

    public function get_name(): string
    {
        return 'null';
    }
}
