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
use Behat\Testwork\Tester\Setup\Setup;
/**
 * Represents an event right after a suite setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Suite_Setup extends Suite_Tested implements After_Setup
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(Environment $env, private readonly Specification_Iterator $iterator, private readonly Setup $setup)
    {
        parent::__construct($env);
    }
    public function get_specification_iterator(): Specification_Iterator
    {
        return $this->iterator;
    }
    /**
     * Returns current test setup.
     */
    public function get_setup(): Setup
    {
        return $this->setup;
    }
}