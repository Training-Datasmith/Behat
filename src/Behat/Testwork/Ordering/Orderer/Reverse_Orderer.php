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

use Behat\Testwork\Specification\Specification_Array_Iterator;

/**
 * Prioritises Suites and Features into reverse order.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class Reverse_Orderer implements Orderer
{
    /**
     * @template T
     *
     * @return list<Specification_Iterator<T>>
     */
    public function order(array $scenario_iterators): array
    {
        $ordered_features = $this->order_features($scenario_iterators);

        return $this->order_suites($ordered_features);
    }

    /**
     * @template T
     *
     * @return list<Specification_Iterator<T>>
     */
    private function order_features(array $scenario_iterators): array
    {
        $ordered_suites = [];
        foreach ($scenario_iterators as $scenario_iterator) {
            $ordered_specifications = array_reverse(iterator_to_array($scenario_iterator));
            $ordered_suites[] = new Specification_Array_Iterator($scenario_iterator->get_suite(), $ordered_specifications);
        }

        return $ordered_suites;
    }

    /**
     * @template T
     *
     * @return Specification_Iterator<T>[]
     */
    private function order_suites(array $ordered_suites): array
    {
        return array_reverse($ordered_suites);
    }

    public function get_name(): string
    {
        return 'reverse';
    }
}
