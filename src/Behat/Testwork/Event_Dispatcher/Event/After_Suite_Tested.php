<?php

declare(strict_types=1);
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
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event in which suite was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Suite_Tested extends Suite_Tested implements After_Tested
{
    /**
     * Initializes event.
     *
     * @param Specification_Iterator<mixed> $iterator
     */
    public function __construct(Environment $env, private readonly Specification_Iterator $iterator, private readonly Test_Result $result, private readonly Teardown $teardown)
    {
        parent::__construct($env);
    }

    public function get_specification_iterator(): Specification_Iterator
    {
        return $this->iterator;
    }

    /**
     * Returns current test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     */
    public function get_teardown(): Teardown
    {
        return $this->teardown;
    }
}
