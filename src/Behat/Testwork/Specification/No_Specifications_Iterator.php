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

use Behat\Testwork\Suite\Suite;
use Empty_Iterator;
/**
 * Represents empty specification iterator.
 *
 * Return an instance of this class from locator if no specifications are found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationIterator<never>
 */
final class No_Specifications_Iterator extends Empty_Iterator implements Specification_Iterator
{
    /**
     * Initializes iterator.
     */
    public function __construct(private readonly Suite $suite)
    {
    }
    /**
     * Returns suite that was used to load subjects.
     */
    public function get_suite(): Suite
    {
        return $this->suite;
    }
}