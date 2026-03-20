<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Represents a scope for AfterSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class After_Suite_Scope implements Suite_Scope, After_Test_Scope
{
    /**
     * Initializes scope.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(private Environment $environment, private Specification_Iterator $iterator, private Test_Result $result)
    {
    }
    public function get_name(): string
    {
        return self::AFTER;
    }
    public function get_suite()
    {
        return $this->environment->get_suite();
    }
    public function get_environment(): Environment
    {
        return $this->environment;
    }
    public function get_specification_iterator(): Specification_Iterator
    {
        return $this->iterator;
    }
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
}