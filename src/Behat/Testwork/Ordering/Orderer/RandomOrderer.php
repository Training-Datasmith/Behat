<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Ordering\Orderer;

use Behat\Testwork\Specification\Specification_Array_Iterator;
use Behat\Testwork\Specification\Specification_Iterator;
/**
 * Prioritises Suites and Features into random order.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class Random_Orderer implements Orderer
{
    /**
     * @template T
     *
     * @param SpecificationIterator<T>[] $scenarioIterators
     *
     * @return list<SpecificationIterator<T>>
     */
    public function order(array $scenario_iterators): array
    {
        $ordered_features = $this->order_features($scenario_iterators);
        shuffle($ordered_features);
        return $ordered_features;
    }
    /**
     * @template T
     *
     * @param SpecificationIterator<T>[] $scenarioIterators
     *
     * @return list<SpecificationIterator<T>>
     */
    private function order_features(array $scenario_iterators): array
    {
        $ordered_suites = [];
        foreach ($scenario_iterators as $scenario_iterator) {
            $ordered_specifications = iterator_to_array($scenario_iterator);
            shuffle($ordered_specifications);
            $ordered_suites[] = new Specification_Array_Iterator($scenario_iterator->get_suite(), $ordered_specifications);
        }
        return $ordered_suites;
    }
    public function get_name(): string
    {
        return 'random';
    }
}