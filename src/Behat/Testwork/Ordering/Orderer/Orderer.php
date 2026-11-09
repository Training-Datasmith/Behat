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
 * Algorithm for prioritising Specification execution.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
interface Orderer
{
    /**
     * @template T
     *
     * @return Specification_Iterator<T>[]
     */
    public function order(array $scenario_iterators);

    /**
     * @return string
     */
    public function get_name();
}
