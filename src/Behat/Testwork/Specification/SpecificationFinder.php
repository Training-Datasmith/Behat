<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Specification;

use Behat\Testwork\Specification\Locator\Specification_Locator;
use Behat\Testwork\Suite\Suite;
/**
 * Finds test specifications for provided suites using registered locators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template T
 */
final class Specification_Finder
{
    /**
     * @var SpecificationLocator<T>[]
     */
    private array $specification_locators = [];
    /**
     * Registers specification locator.
     *
     * @param SpecificationLocator<T> $locator
     */
    public function register_specification_locator(Specification_Locator $locator): void
    {
        $this->specification_locators[] = $locator;
    }
    /**
     * Returns array of strings representing examples of supported specification locators.
     *
     * @return string[]
     */
    public function get_example_locators(): array
    {
        $examples = [];
        foreach ($this->specification_locators as $locator) {
            $examples = array_merge($examples, $locator->get_locator_examples());
        }
        return $examples;
    }
    /**
     * Finds all specifications for all provided suites matching provided locator and wraps them into a spec iterator.
     *
     * @param Suite[]     $suites
     *
     * @return list<SpecificationIterator<T>>
     */
    public function find_suites_specifications(array $suites, ?string $locator = null): array
    {
        $iterators = [];
        foreach ($suites as $suite) {
            $iterators = array_merge($iterators, $this->find_suite_specifications($suite, $locator));
        }
        return $iterators;
    }
    /**
     * Creates suite specification iterator for provided locator.
     *
     *
     * @return list<SpecificationIterator<T>>
     */
    private function find_suite_specifications(Suite $suite, ?string $locator = null): array
    {
        $iterators = [];
        foreach ($this->specification_locators as $specification_locator) {
            $iterators[] = $specification_locator->locate_specifications($suite, $locator);
        }
        return $iterators;
    }
}