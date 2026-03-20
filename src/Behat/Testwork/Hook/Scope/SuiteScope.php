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

use Behat\Testwork\Specification\Specification_Iterator;
/**
 * Represents a suite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Suite_Scope extends Hook_Scope
{
    public const BEFORE = 'suite.before';
    public const AFTER = 'suite.after';
    /**
     * Returns specification iterator.
     *
     * @return SpecificationIterator<mixed>
     */
    public function get_specification_iterator();
}