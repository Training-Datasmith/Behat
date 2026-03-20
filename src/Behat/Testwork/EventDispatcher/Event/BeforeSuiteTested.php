<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Event_Dispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\Specification_Iterator;
/**
 * Represents an event in which suite is prepared to be tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Suite_Tested extends Suite_Tested implements Before_Tested
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(Environment $env, private readonly Specification_Iterator $iterator)
    {
        parent::__construct($env);
    }
    public function get_specification_iterator(): Specification_Iterator
    {
        return $this->iterator;
    }
}