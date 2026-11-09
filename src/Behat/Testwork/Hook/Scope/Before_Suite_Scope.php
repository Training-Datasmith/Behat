<?php

declare(strict_types=1);
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

/**
 * Represents a scope for Before_Suite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Before_Suite_Scope implements Suite_Scope
{
    /**
     * Initializes scope.
     *
     * @param Specification_Iterator<mixed> $iterator
     */
    public function __construct(private Environment $environment, private Specification_Iterator $iterator)
    {
    }

    public function get_name(): string
    {
        return self::BEFORE;
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
}
